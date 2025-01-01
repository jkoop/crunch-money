<?php

namespace App\Models;

use App\Casts\Date;
use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([OwnedScope::class, PeriodScope::class])]
final class Transaction extends Model {
	protected $guarded = [];

	protected $casts = [
		"date" => Date::class,
		"is_system" => "boolean",
		"amount" => "decimal:2",
	];

	public function owner() {
		return $this->belongsTo(User::class, "owner_id");
	}

	public function budget() {
		return $this->belongsTo(Budget::class)->withoutGlobalScope(PeriodScope::class);
	}

	public function fund() {
		return $this->belongsTo(Fund::class);
	}

	public function period() {
		return $this->belongsTo(Period::class);
	}
}
