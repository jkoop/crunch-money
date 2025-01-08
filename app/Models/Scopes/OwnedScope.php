<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

final class OwnedScope implements Scope {
	public function apply(Builder $builder, Model $model): void {
		$builder->where("owner_id", Auth::user()->id);
	}
}
