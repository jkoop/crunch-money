<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([OwnedScope::class])]
final class Transaction extends Model {
	protected $guarded = [];

	protected $casts = [
		"date" => "date",
		"is_system" => "boolean",
		"amount" => "decimal:2",
	];

	public function owner() {
		return $this->belongsTo(User::class, "owner_id");
	}

	public function budget() {
		return $this->belongsTo(Budget::class);
	}

	public function fund() {
		return $this->belongsTo(Fund::class);
	}
}
