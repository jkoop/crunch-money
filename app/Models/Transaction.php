<?php

namespace App\Models;

use App\Casts\DateCast;
use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

#[ScopedBy([OwnedScope::class, PeriodScope::class])]
final class Transaction extends Model {
	use HasFactory;

	protected $guarded = [];

	protected $casts = [
		"date" => DateCast::class,
		"is_system" => "boolean",
		"amount" => "decimal:2",
	];

	public function owner() {
		return $this->belongsTo(User::class, "owner_id");
	}

	public function budget() {
		return $this->belongsTo(Budget::class)->withoutGlobalScopes();
	}

	public function fund() {
		return $this->belongsTo(Fund::class);
	}

	public function period() {
		return $this->belongsTo(Period::class);
	}

	public function getDescription(): HtmlString {
		return $this->is_system
			? new HtmlString("<i>" . e($this->description) . "</i>")
			: new HtmlString(e($this->description));
	}
}
