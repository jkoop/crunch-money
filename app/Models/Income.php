<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([OwnedScope::class, PeriodScope::class])]
final class Income extends Model {
	protected $guarded = [];
	protected $visible = ["id", "name", "amount"];

	public function owner() {
		return $this->belongsTo(User::class);
	}

	public function period() {
		return $this->belongsTo(Period::class)->withoutGlobalScopes();
	}
}
