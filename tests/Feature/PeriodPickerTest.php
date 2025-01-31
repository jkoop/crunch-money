<?php

namespace Tests\Feature;

use App\Models\Period;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PeriodPickerTest extends TestCase {
	#[Test]
	public function listIsSorted(): void {
		Period::factory()
			->for($this->user, "owner")
			->create(["start" => "2019-12-01", "end" => "2019-12-31"]);
		Period::factory()
			->for($this->user, "owner")
			->create(["start" => "2020-02-01", "end" => "2020-02-29"]);

		$this->get("/b")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSeeTextInOrder(["Feb 1 2020", "Jan 1 2020", "Dec 1 2019"]);
	}

	#[Test]
	public function listSelectsCorrectPeriod(): void {
		Period::factory()
			->for($this->user, "owner")
			->create(["start" => "2019-12-01", "end" => "2019-12-31"]);

		$this->get("/b")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSeeInOrder(["selected", "Jan 1 2020", "Dec 1 2019"]);
	}

	#[Test]
	public function submissionWorks(): void {
		$period = Period::factory()
			->for($this->user, "owner")
			->create(["start" => "2019-12-01", "end" => "2019-12-31"]);

		$this->post("/set-period", [
			"period_id" => $period->id,
		])
			->assertSessionHasNoErrors()
			->assertSessionHas("period_id", $period->id)
			->assertRedirect();
	}
}
