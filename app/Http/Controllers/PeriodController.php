<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Fund;
use App\Models\Income;
use App\Models\Period;
use App\Models\Scopes\PeriodScope;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class PeriodController extends Controller {
	public function get(Request $request, string $start_date) {
		$period = Period::where("start", $start_date)->firstOrFail();

		$budgets = $period
			->budgets()
			->with("transactions", function (HasMany $builder) use ($period): void {
				$builder->where("period_id", $period->id);
			})
			->get()
			->map(
				fn(Budget $budget) => [
					"id" => $budget->id,
					"name" => $budget->name,
					"amount" => $budget->transactions->where("period_id", $period->id)->first()?->amount,
				],
			);

		$funds = Fund::withTrashed()
			->where(function (Builder $builder) use ($period): void {
				$builder->where("deleted_at", null)->orWhere("deleted_at", ">=", $period->start);
			})
			->with("transactions", function (HasMany $builder) use ($period): void {
				$builder->where("period_id", $period->id);
			})
			->get()
			->map(
				fn(Fund $fund) => [
					"id" => $fund->id,
					"name" => $fund->name,
					"amount" => $fund->transactions->where("period_id", $period->id)->first()?->amount,
				],
			);

		return view("periods.edit", compact("period", "budgets", "funds"));
	}

	public function post(Request $request, string $start_date) {
		if ($start_date == "new") {
			$period = new Period(["owner_id" => Auth::id()]);
		} else {
			$period = Period::where("start", $start_date)->firstOrFail();
		}

		$request->validate([
			"start" => "required|date",
			"end" => "required|date|after:start",
			"incomes" => "required|array|min:1",
			"incomes.*.name" => "required|string|max:255",
			"incomes.*.amount" => "required|numeric|min:0",
			"budgets" => "required|array|min:1",
			"budgets.*.name" => "required|string|max:255",
			"budgets.*.amount" => "required|numeric|min:0",
			"funds" => "required|array|min:1",
			"funds.*.name" => "required|string|max:255",
			"funds.*.amount" => "required|numeric",
		]);

		$period->start = $request->start;
		$period->end = $request->end;
		$period->save();

		$incomeIds = collect($request->incomes)
			->pluck("id")
			->toArray();

		foreach ($period->incomes as $income) {
			if (!in_array($income->id, $incomeIds)) {
				$income->delete();
			}
		}

		foreach ($request->incomes as $income) {
			// [id, name, amount]
			$existingIncome = Income::withoutGlobalScope(PeriodScope::class)->find($income["id"]);
			if ($existingIncome == null || $existingIncome->period_id != $period->id) {
				Income::create([
					"owner_id" => Auth::id(),
					"period_id" => $period->id,
					"name" => $income["name"],
					"amount" => $income["amount"],
				]);
			} else {
				$existingIncome->update([
					"name" => $income["name"],
					"amount" => $income["amount"],
				]);
			}
		}

		$budgetIds = collect($request->budgets)
			->pluck("id")
			->toArray();

		foreach ($period->budgets as $budget) {
			if (!in_array($budget->id, $budgetIds)) {
				$budget->delete();
			}
		}

		foreach ($request->budgets as $budget) {
			// [id, name, amount]
			$existingBudget = Budget::withoutGlobalScope(PeriodScope::class)->find($budget["id"]);
			if ($existingBudget == null || $existingBudget->period_id != $period->id) {
				$slug = Str::slug($budget["name"]);
				$counter = "";
				while (true) {
					try {
						$existingBudget = Budget::create([
							"owner_id" => Auth::id(),
							"period_id" => $period->id,
							"name" => $budget["name"],
							"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
						]);
						break;
					} catch (UniqueConstraintViolationException $e) {
						if (!Str::of($e)->contains("UNIQUE constraint failed: budgets.owner_id, budgets.slug")) {
							throw $e;
						}
						if ($counter == "") {
							$counter = -1;
						}
						$counter--;
					}
				}
			} else {
				$slug = Str::slug($budget["name"]);
				$counter = "";
				while (true) {
					try {
						$existingBudget->update([
							"name" => $budget["name"],
							"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
						]);
						break;
					} catch (UniqueConstraintViolationException $e) {
						if (!Str::of($e)->contains("UNIQUE constraint failed: budgets.owner_id, budgets.slug")) {
							throw $e;
						}
						if ($counter == "") {
							$counter = -1;
						}
						$counter--;
					}
				}
			}

			$existingTransaction = Transaction::withoutGlobalScope(PeriodScope::class)
				->where("budget_id", $existingBudget->id)
				->where("period_id", $period->id)
				->first();
			if ($existingTransaction == null) {
				Transaction::create([
					"owner_id" => Auth::id(),
					"period_id" => $period->id,
					"budget_id" => $existingBudget->id,
					"amount" => $budget["amount"],
					"description" => "@todo come up with a better description",
					"is_system" => true,
					"date" => $period->start,
				]);
			} else {
				$existingTransaction->update([
					"amount" => $budget["amount"],
					"date" => $period->start,
				]);
			}
		}

		$fundIds = collect($request->funds)
			->pluck("id")
			->toArray();

		foreach (Fund::withTrashed()->get() as $fund) {
			if (!in_array($fund->id, $fundIds)) {
				$fund->update([
					"deleted_at" => Carbon::minValue(Carbon::now(), $period->end, $fund->deleted_at),
				]);
			}
		}

		foreach ($request->funds as $fund) {
			// [id, name, amount]
			$existingFund = Fund::find($fund["id"]);
			if ($existingFund == null) {
				$slug = Str::slug($fund["name"]);
				$counter = "";
				while (true) {
					try {
						$existingFund = Fund::create([
							"owner_id" => Auth::id(),
							"name" => $fund["name"],
							"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
						]);
						break;
					} catch (UniqueConstraintViolationException $e) {
						if (!Str::of($e)->contains("UNIQUE constraint failed: funds.owner_id, funds.slug")) {
							throw $e;
						}
						if ($counter == "") {
							$counter = -1;
						}
						$counter--;
					}
				}
			} else {
				$slug = Str::slug($fund["name"]);
				$counter = "";
				while (true) {
					try {
						$existingFund->update([
							"name" => $fund["name"],
							"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
						]);
						break;
					} catch (UniqueConstraintViolationException $e) {
						if (!Str::of($e)->contains("UNIQUE constraint failed: funds.owner_id, funds.slug")) {
							throw $e;
						}
						if ($counter == "") {
							$counter = -1;
						}
						$counter--;
					}
				}
			}

			$existingTransaction = Transaction::withoutGlobalScope(PeriodScope::class)
				->where("fund_id", $existingFund->id)
				->where("period_id", $period->id)
				->first();
			if ($existingTransaction == null) {
				Transaction::create([
					"owner_id" => Auth::id(),
					"period_id" => $period->id,
					"fund_id" => $existingFund->id,
					"amount" => $fund["amount"],
					"description" => "@todo come up with a better description",
					"is_system" => true,
					"date" => $period->start,
				]);
			} else {
				$existingTransaction->update([
					"amount" => $fund["amount"],
					"date" => $period->start,
				]);
			}
		}

		return redirect()->route("periods.get", $period->start->format("Y-m-d"));
	}
}
