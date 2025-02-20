<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory {
	public function definition(): array {
		return [
			"name" => fake()->name(),
			"token" => base64_encode(random_bytes(24)),
		];
	}
}
