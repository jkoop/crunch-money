<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fund>
 */
final class FundFactory extends Factory {
	public function definition(): array {
		$name = fake()->words(asText: true);

		return [
			"name" => $name,
			"slug" => Str::slug($name),
			"owner_id" => User::factory(),
		];
	}
}
