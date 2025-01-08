<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Fund;
use App\Models\Period;
use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;

final class DashboardController extends Controller {
	public function get() {
		$stats = [
			"budgets" => $this->getBudgets(),
			"funds" => $this->getFunds(),
			"periods" => $this->getPeriods(),
			"transactions" => $this->getTransactions(),
			"users" => $this->getUsers(),
		];
		return view("admin.dashboard", compact("stats"));
	}

	private static function getToday(): string {
		return once(fn() => Carbon::now()->format("Y-m-d"));
	}

	private function getBudgets(): array {
		return [
			"current" => Budget::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])
				->whereIn(
					"period_id",
					Period::withoutGlobalScope(OwnedScope::class)
						->where("start", "<=", self::getToday())
						->where("end", ">=", self::getToday())
						->select("id"),
				)
				->count(),
			"orphaned" => Budget::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])
				->whereNotIn("owner_id", User::select("id"))
				->count(),
			"total" => Budget::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])->count(),
		];
	}

	private function getFunds(): array {
		return [
			"orphaned" => Fund::withoutGlobalScope(OwnedScope::class)
				->whereNotIn("owner_id", User::select("id"))
				->count(),
			"total" => Fund::withoutGlobalScope(OwnedScope::class)->count(),
		];
	}

	private function getTransactions(): array {
		return [
			"current" => Transaction::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])
				->whereIn(
					"period_id",
					Period::withoutGlobalScope(OwnedScope::class)
						->where("start", "<=", self::getToday())
						->where("end", ">=", self::getToday())
						->select("id"),
				)
				->count(),
			"orphaned" => Transaction::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])
				->whereNotIn("owner_id", User::select("id"))
				->count(),
			"total" => Transaction::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])->count(),
		];
	}

	private function getPeriods(): array {
		return [
			"current" => Period::withoutGlobalScope(OwnedScope::class)
				->where("start", "<=", self::getToday())
				->where("end", ">=", self::getToday())
				->count(),
			"orphaned" => Period::withoutGlobalScope(OwnedScope::class)
				->whereNotIn("owner_id", User::select("id"))
				->count(),
			"total" => Period::withoutGlobalScope(OwnedScope::class)->count(),
		];
	}

	private function getUsers(): array {
		return [
			"basic" => User::where("is_admin", 0)->count(),
			"admins" => User::where("is_admin", 1)->count(),
			"total" => User::count(),
		];
	}
}
