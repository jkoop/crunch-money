<?php

namespace Database\Factories;

use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
final class BudgetFactory extends Factory {
	public function definition(): array {
		$name = fake()->words(asText: true);

		return [
			"name" => $name,
			"slug" => Str::slug($name),
			"amount" => (string) fake()->randomFloat(2, 0, 9999.99),
			"period_id" => Period::factory(),
			"owner_id" => User::factory(),
		];
	}
}
