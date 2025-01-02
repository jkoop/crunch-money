<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([OwnedScope::class, PeriodScope::class])]
final class Budget extends Model {
	use SoftDeletes;

	protected $guarded = [];

	public function owner() {
		return $this->belongsTo(User::class, "owner_id");
	}

	public function period() {
		return $this->belongsTo(Period::class)->withoutGlobalScope(PeriodScope::class);
	}

	public function transactions() {
		return $this->hasMany(Transaction::class)->withoutGlobalScope(PeriodScope::class);
	}

	public function getBalanceAttribute() {
		return $this->transactions->sum("amount");
	}
}
