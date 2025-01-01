<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Period;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

final class PeriodController extends Controller {
	public function get(Request $request, string $start_date) {
		$period = Period::where("start", $start_date)->firstOrFail();
		$funds = Fund::withTrashed()
			->where(function (Builder $builder) use ($period): void {
				$builder->where("deleted_at", null)->orWhere("deleted_at", ">=", $period->start);
			})
			->with("transactions", function (HasMany $builder): void {
				$builder->withoutGlobalScope(PeriodScope::class);
			})
			->get()
			->map(
				fn(Fund $fund) => [
					"id" => $fund->id,
					"name" => $fund->name,
					"balance" => $fund->balance,
					"amount" => $fund
						->transactions()
						->where("period_id", $period->id)
						->first()
						?->amount(),
				],
			);

		return view("periods.edit", compact("period", "funds"));
	}
}
