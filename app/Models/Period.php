<?php

namespace App\Models;

use App\Casts\Date;
use App\Models\Scopes\OwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

#[ScopedBy([OwnedScope::class])]
final class Period extends Model {
	protected $guarded = [];

	protected $casts = [
		"start" => Date::class,
		"end" => Date::class,
	];

	public function owner() {
		return $this->belongsTo(User::class);
	}

	public function incomes() {
		return $this->hasMany(Income::class)->withoutGlobalScopes();
	}

	public function transactions() {
		return $this->hasMany(Transaction::class)->withoutGlobalScopes();
	}

	public function budgets() {
		return $this->hasMany(Budget::class)->withoutGlobalScopes();
	}

	public function funds() {
		return $this->belongsToMany(Fund::class)
			->withoutGlobalScopes()
			->withPivot("amount");
	}

	public static function current(): Period {
		return once(function () {
			if (Session::has("period_id")) {
				$period = Period::find(Session::get("period_id"));
				if ($period != null) {
					return $period;
				}
				Session::forget("period_id");
			}

			$now = Carbon::now();
			$period = Period::where("start", "<=", $now)->where("end", ">=", $now)->first();

			if ($period != null) {
				Session::put("period_id", $period->id);
				return $period;
			}

			$period = Period::create([
				"owner_id" => Auth::user()->id,
				"start" => (clone $now)->startOfMonth(),
				"end" => (clone $now)->endOfMonth(),
			]);

			Session::put("period_id", $period->id);
			return $period;
		});
	}

	public function previousPeriod(): Builder {
		return self::where("end", (clone $this->start)->subDay()->format("Y-m-d"));
	}

	public function getCarryoverAttribute(self|int $excludePeriod = null): float {
		if ($excludePeriod instanceof self) {
			$excludePeriod = $excludePeriod->id;
		}

		$previousPeriod = $this->previousPeriod()
			->with(["transactions" => fn($builder) => $builder->whereHas("budget")])
			->where("id", "!=", $excludePeriod)
			->first();

		if ($previousPeriod == null) {
			return 0;
		}

		return $previousPeriod->transactions->sum("amount");
	}
}
