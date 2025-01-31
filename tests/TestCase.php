<?php

namespace Tests;

use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use RefreshDatabase;

	protected Period $period;
	protected User $user;

	public function setup(): void {
		parent::setUp();

		$this->user = User::factory()->create();
		$this->period = Period::factory()
			->for($this->user, "owner")
			->create([
				"start" => "2020-01-01",
				"end" => "2020-01-31",
			]);

		$this->logInAs($this->user);
	}

	public function logInAs(User $user): void {
		$this->get("/logout")->assertRedirect("/login");

		$this->get("/login")->assertOk()->assertSessionHasNoErrors()->assertSeeText("Guest");

		$this->post("/login", [
			"token" => $user->token,
		])->assertRedirect("/b");

		$this->get("/b")->assertSessionHasNoErrors()->assertOk()->assertSeeText($user->name);
	}
}
