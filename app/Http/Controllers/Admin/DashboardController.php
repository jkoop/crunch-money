<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Fund;
use App\Models\Period;
use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use App\Models\Transaction;
use App\Models\User;

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

	private function getBudgets(): array {
		return [
			"current" => Budget::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])
				->whereIn(
					"period_id",
					Period::withoutGlobalScope(OwnedScope::class)
						->where("start", "<=", Date::today())
						->where("end", ">=", Date::today())
						->select("id"),
				)
				->count(),
			"total" => Budget::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])->count(),
		];
	}

	private function getFunds(): array {
		return [
			"total" => Fund::withoutGlobalScope(OwnedScope::class)->count(),
		];
	}

	private function getTransactions(): array {
		return [
			"current" => Transaction::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])
				->whereIn(
					"period_id",
					Period::withoutGlobalScope(OwnedScope::class)
						->where("start", "<=", Date::today())
						->where("end", ">=", Date::today())
						->select("id"),
				)
				->count(),
			"total" => Transaction::withoutGlobalScopes([OwnedScope::class, PeriodScope::class])->count(),
		];
	}

	private function getPeriods(): array {
		return [
			"current" => Period::withoutGlobalScope(OwnedScope::class)
				->where("start", "<=", Date::today())
				->where("end", ">=", Date::today())
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
