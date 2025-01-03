<?php

namespace App\Models;

use App\Models\Scopes\OwnedScope;
use App\Models\Scopes\PeriodScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ScopedBy([OwnedScope::class, PeriodScope::class])]
final class Fund extends Model {
	protected $guarded = [];

	public function owner(): BelongsTo {
		return $this->belongsTo(User::class, "owner_id");
	}

	public function transactions(): HasMany {
		return $this->hasMany(Transaction::class)->withoutGlobalScope(PeriodScope::class);
	}

	public function periods(): BelongsToMany {
		return $this->belongsToMany(Period::class);
	}

	public function getBalanceAttribute(): float {
		return $this->transactions->sum("amount");
	}

	public static function generateSlug(string $name, ?Period $onlyConsiderPeriod = null): string {
		$slug = Str::slug($name);

		if ($slug == "") {
			$slug = "empty";
		}

		if ($slug == "new") {
			$slug = "new-fund";
		}

		$collisionCheckBuilder = self::withoutGlobalScope(PeriodScope::class)->where("slug", $slug);
		if ($onlyConsiderPeriod != null) {
			$collisionCheckBuilder->whereHas(
				"periods",
				fn(Builder $builder) => $builder->where("id", $onlyConsiderPeriod->id),
			);
		}

		$collisionCounterBuilder = self::withoutGlobalScope(PeriodScope::class)->where("slug", "like", $slug . "%");
		if ($onlyConsiderPeriod != null) {
			$collisionCounterBuilder->whereHas(
				"periods",
				fn(Builder $builder) => $builder->where("id", $onlyConsiderPeriod->id),
			);
		}

		if ($collisionCheckBuilder->exists()) {
			$slug .= "-" . $collisionCounterBuilder->count();
		}

		return $slug;
	}
}
