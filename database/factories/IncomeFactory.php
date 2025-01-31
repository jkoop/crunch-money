<?php

namespace Database\Factories;

use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Income>
 */
final class IncomeFactory extends Factory {
	public function definition(): array {
		return [
			"name" => fake()->words(asText: true),
			"amount" => fake()->randomFloat(2, 0, 9999.99),
			"period_id" => Period::factory(),
			"owner_id" => User::factory(),
		];
	}
}
