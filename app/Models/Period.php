<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

#[ScopedBy([OwnedScope::class])]
final class Period extends Model {
	protected $guarded = [];

	protected $casts = [
		"start" => "date",
		"end" => "date",
	];

	public function transactions() {
		return $this->hasMany(Transaction::class);
	}

	public function budgets() {
		return $this->hasMany(Budget::class);
	}

	public static function current(): Period {
		if (Session::has("period_id")) {
			return Period::find(Session::get("period_id"));
		}

		$now = Carbon::now();
		$period = Period::where("start", "<=", $now)->where("end", ">=", $now)->first();

		if ($period == null) {
			$period = Period::create([
				"owner_id" => Auth::id(),
				"start" => $now->startOfMonth(),
				"end" => $now->endOfMonth(),
			]);
		}

		Session::put("period_id", $period->id);
		return $period;
	}
}
