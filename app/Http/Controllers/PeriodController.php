<?php

namespace App\Http\Controllers;

use App\Exceptions\ImpossibleStateException;
use App\Models\Budget;
use App\Models\Fund;
use App\Models\Income;
use App\Models\Period;
use App\Models\Scopes\PeriodScope;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

final class PeriodController extends Controller {
	public function carryover(Request $request): Response {
		$request->validate([
			"new_start" => "required|date",
		]);

		$exclude = $request->exclude;
		if ($exclude == "null") {
			$exclude = null;
		}

		return response(
			(new Period(["owner_id" => Auth::user()->id, "start" => $request->new_start]))->getCarryoverAttribute(
				excludePeriod: $exclude,
			),
			headers: [
				"Content-Type" => "application/json",
			],
		);
	}

	public function get(Request $request, string $start_date = "new") {
		if ($start_date == "new") {
			$slug = "new";
			$period =
				Period::orderByDesc("start")->with("incomes", "budgets", "funds")->first() ??
				throw new ImpossibleStateException();
			$oldStart = $period->start;
			$oldEnd = $period->end;
			$oldDuration = round($oldStart->diffInDays($oldEnd));
			$period->start = (clone $oldEnd)->addDay();
			$period->end = (clone $oldEnd)->addDays($oldDuration);
		} else {
			$slug = $start_date;
			$period = Period::where("start", $start_date)->firstOrFail();
		}

		$budgets = $period->budgets()->get()->map(
			fn(Budget $budget) => [
				"id" => $budget->id,
				"name" => $budget->name,
				"amount" => $budget->amount,
			],
		);

		$funds = $period->funds()->get()->map(
			fn(Fund $fund) => [
				"id" => $fund->id,
				"name" => $fund->name,
				"amount" => $fund->pivot->amount,
			],
		);

		if ($start_date == "new") {
			$period->id = null;
		}

		return view("periods.edit", compact("period", "budgets", "funds", "slug"));
	}

	public function post(Request $request, string $start_date) {
		return Cache::lock("user:" . Auth::user()->id)->block(5, function () use ($request, $start_date) {
			if ($start_date == "new") {
				$period = new Period(["owner_id" => Auth::user()->id]);
			} else {
				$period = Period::where("start", $start_date)->firstOrFail();
			}

			$request->validate([
				"start" => "required|date",
				"end" => "required|date|after:start",
				"incomes" => "required|array|min:1",
				"incomes.*.name" => "required|string|max:255",
				"incomes.*.amount" => "required|string|max:255|regex:/^[\d,]+(\.[\d,]{1,2})?$/",
				"budgets" => "required|array|min:1",
				"budgets.*.name" => "required|string|max:255",
				"budgets.*.amount" => "required|string|max:255|regex:/^[\d,]+(\.[\d,]{1,2})?%?$/",
				"funds" => "required|array|min:1",
				"funds.*.name" => "required|string|max:255",
				"funds.*.amount" => "required|string|max:255|regex:/^-?[\d,]+(\.[\d,]{1,2})?%?$/",
			]);

			$period->start = $request->start;
			$period->end = $request->end;
			self::savePeriod($period);

			$warnings = [];

			if ($period->start->format("Y-m-d") != $request->start) {
				$warnings[] =
					"The start date was already taken, so we changed it to " . $period->start->format("Y-m-d");
			}

			if ($period->end->format("Y-m-d") != $request->end) {
				$warnings[] = "The end date was already taken, so we changed it to " . $period->end->format("Y-m-d");
			}

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
						"owner_id" => Auth::user()->id,
						"period_id" => $period->id,
						"name" => $income["name"],
						"amount" => self::strToFloat($income["amount"]),
					]);
				} else {
					$existingIncome->update([
						"name" => $income["name"],
						"amount" => self::strToFloat($income["amount"]),
					]);
				}
			}

			$budgetIds = collect($request->budgets)
				->pluck("id")
				->toArray();

			$csv = fopen("php://memory", "rw");
			fputcsv($csv, ["Date", "Budget", "Amount", "Description"]);

			foreach ($period->budgets()->get() as $budget) {
				if (!in_array($budget->id, $budgetIds)) {
					$budget
						->transactions()
						->where("is_system", false)
						->get()
						->map(
							fn($transaction) => fputcsv($csv, [
								$transaction->date->format("Y-m-d"),
								$budget->name,
								$transaction->amount,
								$transaction->description,
							]),
						);
					$budget->delete();
				}
			}

			rewind($csv);
			$csv = stream_get_contents($csv);
			if (str_contains(trim($csv), "\n")) {
				Session::push("downloads", [
					"id" => Ulid::generate(), // for identifying when deleting later
					"name" => "deleted-transactions.csv",
					"content" => $csv,
				]);
			}
			unset($csv);

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
								"owner_id" => Auth::user()->id,
								"period_id" => $period->id,
								"name" => $budget["name"],
								"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
								"amount" => $budget["amount"],
							]);
							break;
						} catch (UniqueConstraintViolationException $e) {
							if (
								!Str::of($e)->contains(
									"UNIQUE constraint failed: budgets.owner_id, budgets.period_id, budgets.slug",
								)
							) {
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
								"amount" => $budget["amount"],
							]);
							break;
						} catch (UniqueConstraintViolationException $e) {
							if (
								!Str::of($e)->contains(
									"UNIQUE constraint failed: budgets.owner_id, budgets.period_id, budgets.slug",
								)
							) {
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
						"owner_id" => Auth::user()->id,
						"period_id" => $period->id,
						"budget_id" => $existingBudget->id,
						"amount" => self::amountOfIncome($budget["amount"], $period),
						"description" => "deposit via period",
						"is_system" => true,
						"date" => $request->start,
					]);
				} else {
					$existingTransaction->update([
						"amount" => self::amountOfIncome($budget["amount"], $period),
						"date" => $request->start,
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
							"owner_id" => Auth::user()->id,
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

				try {
					$existingFund->periods()->attach($period->id, ["amount" => $fund["amount"]]);
				} catch (UniqueConstraintViolationException $e) {
					// I'm not a huge fan of this
					DB::transaction(function () use ($existingFund, $period, $fund) {
						$existingFund->periods()->detach($period->id);
						$existingFund->periods()->attach($period->id, ["amount" => $fund["amount"]]);
					});
				}

				$existingTransaction = Transaction::withoutGlobalScope(PeriodScope::class)
					->where("fund_id", $existingFund->id)
					->where("period_id", $period->id)
					->first();

				if ($existingTransaction == null) {
					Transaction::create([
						"owner_id" => Auth::user()->id,
						"period_id" => $period->id,
						"fund_id" => $existingFund->id,
						"amount" => self::amountOfIncome($fund["amount"], $period),
						"description" => $fund["amount"] > 0 ? "deposit via period" : "withdrawal via period",
						"is_system" => true,
						"date" => $request->start,
					]);
				} else {
					$existingTransaction->update([
						"amount" => self::amountOfIncome($fund["amount"], $period),
						"date" => $request->start,
					]);
				}
			}

			return redirect()->route("periods")->with("warnings", $warnings);
		});
	}

	private static function amountOfIncome(string $amount, Period $period): float {
		$wasPercentage = Str::endsWith($amount, "%");
		$amount = Str::replace("%", "", $amount);
		$amount = self::strToFloat($amount);
		if ($wasPercentage) {
			$incomes = $period->incomes()->get();
			$totalIncome = $incomes->sum("amount");
			return round(($amount / 100) * $totalIncome, 2);
		}
		return round($amount, 2);
	}

	private static function strToFloat(string $string): float {
		$string = preg_replace("/[^0-9\.-]/", "", $string);
		$float = (float) $string;
		return $float;
	}

	private static function savePeriod(Period $period): void {
		do {
			try {
				$result = $period->save();
			} catch (\Throwable $e) {
				$result = $e;
			}
			/** @var bool|\Throwable $result */

			if ($result === true) {
				return; // success
			} elseif ($result === false) {
				throw new ImpossibleStateException();
			} elseif (
				$result instanceof QueryException &&
				Str::of($result->getMessage())->startsWith(
					"SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: periods.owner_id, periods.start (",
				)
			) {
				// increment start date and try again
				$period->start = Carbon::parse($period->start)
					->addDay()
					->format("Y-m-d");

				if ($period->start == $period->end) {
					$period->end = Carbon::parse($period->end)
						->addDay()
						->format("Y-m-d");
				}
			} elseif (
				$result instanceof QueryException &&
				Str::of($result->getMessage())->startsWith(
					"SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: periods.owner_id, periods.end (",
				)
			) {
				// increment end date and try again
				$period->end = Carbon::parse($period->end)
					->addDay()
					->format("Y-m-d");
			} else {
				throw $result;
			}
		} while (true);
	}
}
