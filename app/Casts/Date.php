<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final class Date implements CastsAttributes {
	public function get(Model $model, string $key, mixed $value, array $attributes): Carbon {
		return Carbon::parse($value);
	}

	public function set(Model $model, string $key, mixed $value, array $attributes): string {
		if ($value instanceof Carbon) {
			return $value->format("Y-m-d");
		} elseif (is_string($value)) {
			return Carbon::parse($value)->format("Y-m-d");
		}

		throw new \InvalidArgumentException("Invalid date value");
	}
}
