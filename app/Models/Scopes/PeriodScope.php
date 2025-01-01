<?php

namespace App\Models\Scopes;

use App\Models\Period;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class PeriodScope implements Scope {
	public function apply(Builder $builder, Model $model): void {
		// doesn't have period_id column
		if ($model instanceof Transaction) {
			$builder
				->whereHas("budget", function (Builder $builder) {
					$builder->where("period_id", Period::current()->id);
				})
				->orWhereHas("fund", function (Builder $builder) {
					$builder->where("period_id", Period::current()->id);
				});
		} else {
			$builder->where("period_id", Period::current()->id);
		}
	}
}
