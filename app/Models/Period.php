<?php

namespace App\Models;

use App\Casts\Date;
use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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
		return $this->hasMany(Income::class)->withoutGlobalScope(PeriodScope::class);
	}

	public function transactions() {
		return $this->hasMany(Transaction::class)->withoutGlobalScope(PeriodScope::class);
	}

	public function budgets() {
		return $this->hasMany(Budget::class);
	}

	public static function current(): Period {
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
			"owner_id" => Auth::id(),
			"start" => (clone $now)->startOfMonth(),
			"end" => (clone $now)->endOfMonth(),
		]);

		Session::put("period_id", $period->id);
		return $period;
	}
}
