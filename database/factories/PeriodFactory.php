<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
final class PeriodFactory extends Factory {
	public function definition(): array {
		return [
			// intentionally left unset to force the test to set them
			// "start" => ,
			// "end" => ,
			"owner_id" => User::factory(),
		];
	}
}
