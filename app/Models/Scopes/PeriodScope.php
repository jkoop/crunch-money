<?php

namespace App\Models\Scopes;

use App\Models\Period;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class PeriodScope implements Scope {
	public function apply(Builder $builder, Model $model): void {
		$builder->where("period_id", Period::current()->id);
	}
}