<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([OwnedScope::class, PeriodScope::class])]
final class Budget extends Model {
	use HasFactory;

	protected $guarded = [];

	public function owner() {
		return $this->belongsTo(User::class, "owner_id");
	}

	public function period() {
		return $this->belongsTo(Period::class)->withoutGlobalScopes();
	}

	public function transactions() {
		return $this->hasMany(Transaction::class)->withoutGlobalScopes();
	}

	public function getBalanceAttribute() {
		return $this->transactions->sum("amount");
	}
}
