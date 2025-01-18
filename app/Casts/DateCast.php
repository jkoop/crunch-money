<?php

namespace App\Casts;

use App\Helpers\Date;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class DateCast implements CastsAttributes {
	public function get(Model $model, string $key, mixed $value, array $attributes): Date {
		return Date::parse($value);
	}

	public function set(Model $model, string $key, mixed $value, array $attributes): string {
		if ($value instanceof Date) {
			return (string) $value;
		} elseif (is_string($value)) {
			return (string) Date::parse($value);
		}

		throw new \InvalidArgumentException("Invalid date type");
	}
}
