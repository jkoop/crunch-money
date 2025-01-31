<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
final class TransactionFactory extends Factory {
	public function definition(): array {
		return [
			// intentionally left unset to force the test to set it
			// "date" => ,
			// "budget_id" => Budget::factory(),
			// "fund_id" => Fund::factory(),

			"amount" => fake()->randomFloat(2, 0, 999.99),
			"description" => fake()->words(asText: true),
			"period_id" => Period::factory(),
			"owner_id" => User::factory(),
		];
	}
}
