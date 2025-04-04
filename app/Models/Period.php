<?php

namespace App\Models;

use App\Casts\DateCast;
use App\Helpers\Date;
use App\Models\Scopes\OwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

#[ScopedBy([OwnedScope::class])]
final class Period extends Model {
	use HasFactory;

	protected $guarded = [];

	protected $casts = [
		"start" => DateCast::class,
		"end" => DateCast::class,
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
		return $this->belongsToMany(Fund::class)->withoutGlobalScopes()->withPivot("amount");
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

			$today = Date::today();
			$todaysPeriod = Period::where("start", "<=", $today)->where("end", ">=", $today)->first();

			if ($todaysPeriod != null) {
				Session::put("period_id", $todaysPeriod->id);
				return $todaysPeriod;
			}

			$mostRecentPeriod = Period::where("start", "<=", $today)->first();

			if ($mostRecentPeriod != null) {
				Session::put("period_id", $mostRecentPeriod->id);
				return $mostRecentPeriod;
			}

			$newPeriod = Period::create([
				"owner_id" => Auth::user()->id,
				"start" => $today->startOfMonth(),
				"end" => $today->endOfMonth(),
			]);

			Session::put("period_id", $newPeriod->id);
			return $newPeriod;
		});
	}

	public function previousPeriod(): Builder {
		return self::where("end", $this->start->previousDay());
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
