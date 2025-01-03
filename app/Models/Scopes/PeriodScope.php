<?php

namespace App\Models\Scopes;

use App\Models\Period;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class PeriodScope implements Scope {
	public function apply(Builder $builder, Model $model): void {
		$currentPeriodId = Period::current()->id;

		if (method_exists($model, "period")) {
			$builder->whereHas("period", fn(Builder $builder) => $builder->where("id", $currentPeriodId));
		} elseif (method_exists($model, "periods")) {
			$builder->whereHas("periods", fn(Builder $builder) => $builder->where("id", $currentPeriodId));
		}
	}
}
