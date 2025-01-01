<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([OwnedScope::class])]
final class Fund extends Model {
	protected $guarded = [];

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function transactions() {
		return $this->hasMany(Transaction::class);
	}

	public function getBalanceAttribute() {
		return $this->transactions->sum("amount");
	}
}
