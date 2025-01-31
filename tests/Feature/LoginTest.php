<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginTest extends TestCase {
	public function setup(): void {
		parent::setup();

		// the parent's setup logs us in
		$this->get("/logout")->assertSessionHasNoErrors()->assertRedirect();
	}

	#[Test]
	public function loginFailsForMissingUser(): void {
		$this->post("/login", [
			"token" => "no-such-token",
		])
			->assertRedirect()
			->assertSessionHasErrors("token");
	}

	#[Test]
	public function loginSuccessfully(): void {
		$user = User::factory()->create();

		$this->post("/login", [
			"token" => $user->token,
		])->assertRedirect("/b");

		$this->get("/b")->assertSessionHasNoErrors()->assertOk()->assertSeeText($user->name);
	}
}
