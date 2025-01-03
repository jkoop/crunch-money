<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Fund;
use App\Models\Income;
use App\Models\Period;
use App\Models\Scopes\PeriodScope;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class PeriodController extends Controller {
	public function get(Request $request, string $start_date = "new") {
		if ($start_date == "new") {
			$period = new Period(["owner_id" => Auth::id()]);
			$slug = "new";
		} else {
			$period = Period::where("start", $start_date)->firstOrFail();
			$slug = $start_date;
		}

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

		$funds = $period
			->funds()
			->with("transactions", function (HasMany $builder) use ($period): void {
				$builder->where("period_id", $period->id);
			})
			->get()
			->map(
				fn(Fund $fund) => [
					"id" => $fund->id,
					"name" => $fund->name,
					"amount" => $fund->transactions->first()?->amount ?? 0,
				],
			);

		return view("periods.edit", compact("period", "budgets", "funds", "slug"));
	}

	public function post(Request $request, string $start_date) {
		return Cache::lock("user:" . Auth::id())->block(5, function () use ($request, $start_date) {
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

			$warnings = [];

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

			foreach (
				Budget::withTrashed()
					->where("period_id", $period->id)
					->get()
				as $budget
			) {
				if (!in_array($budget->id, $budgetIds)) {
					$budget->update([
						"deleted_at" => Carbon::createFromTimestamp(
							min(
								Carbon::now()->timestamp,
								$period->end->timestamp,
								$budget->deleted_at?->timestamp ?? PHP_INT_MAX,
							),
						),
					]);
				}
			}

			foreach ($request->budgets as $budget) {
				// [id, name, amount]
				$existingBudget = Budget::withoutGlobalScope(PeriodScope::class)->find($budget["id"]);
				if ($existingBudget == null || $existingBudget->period_id != $period->id) {
					$slug = Str::slug($budget["name"]);

					if ($slug == "new") {
						$warnings[] = "Budget name cannot be like 'new'. Automatically generating a new name.";
						$budget["name"] = "New budget";
						$slug = Str::slug($budget["name"]);
					}
					if ($slug == "") {
						$warnings[] = "Budget name cannot be empty. Automatically generating a new name.";
						$budget["name"] = "New budget";
						$slug = Str::slug($budget["name"]);
					}

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

					if ($slug == "new") {
						$warnings[] = "Budget name cannot be like 'new'. Automatically generating a new name.";
						$budget["name"] = "New budget";
						$slug = Str::slug($budget["name"]);
					}
					if ($slug == "") {
						$warnings[] = "Budget name cannot be empty. Automatically generating a new name.";
						$budget["name"] = "New budget";
						$slug = Str::slug($budget["name"]);
					}

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
						"description" => "deposit via period",
						"is_system" => true,
						"date" => "0001-01-01",
					]);
				} else {
					$existingTransaction->update([
						"amount" => $budget["amount"],
					]);
				}
			}

			$fundIds = collect($request->funds)
				->pluck("id")
				->toArray();

			/**
			 * when deleting a fund, detach it from the current period, then check if the fund
			 * has any non-zero transactions. if it doesn't, delete the fund. if it does,
			 * leave it alone.
			 */
			foreach (Fund::withoutGlobalScope(PeriodScope::class)->get() as $fund) {
				if (!in_array($fund->id, $fundIds)) {
					$fund->periods()->detach($period->id);
					$fund
						->transactions()
						->where("period_id", $period->id)
						->delete();

					if ($fund->transactions()->where("amount", "!=", 0)->exists() == false) {
						$fund->delete();
					}
				}
			}

			foreach ($request->funds as $fund) {
				/** @var array{id:int|string,name:string,amount:float|int} $fund */

				$existingFund = Fund::find($fund["id"]);

				/**
				 * when creating a fund, generate a slug for it while only considering funds that
				 * are present in the current period, then check if the slug is absolutely unique.
				 * if it is, create a brand new fund. otherwise, attach the existing fund to the
				 * current period.
				 */
				if ($existingFund == null) {
					$slug = Fund::generateSlug($fund["name"], $period);

					$existingFund = Fund::withoutGlobalScope(PeriodScope::class)
						->where("slug", $slug)
						->first();

					if ($existingFund == null) {
						$existingFund = Fund::create([
							"owner_id" => Auth::id(),
							"name" => $fund["name"],
							"slug" => $slug,
						]);
					}
				} else {
					$slug = Fund::generateSlug($fund["name"]);
					$existingFund->update([
						"name" => $fund["name"],
						"slug" => $slug,
					]);
				}

				$existingFund->periods()->attach($period->id);

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
						"description" => $fund["amount"] > 0 ? "deposit via period" : "withdrawal via period",
						"is_system" => true,
						"date" => "0001-01-01",
					]);
				} else {
					$existingTransaction->update([
						"amount" => $fund["amount"],
					]);
				}
			}

			return redirect()->route("periods")->with("warnings", $warnings);
		});
	}
}
