<?php

namespace Tests\Unit;

use App\Helpers\Date;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DateTest extends TestCase {
	#[Test]
	public function formatBasicToday(): void {
		$date = new Date(date("Y"), date("m"), date("d"));
		$this->assertEquals(actual: $date->format(), expected: date("D M j"));
	}

	#[Test]
	public function formatBasicYesteryear(): void {
		$date = new Date(2024, 01, 01);
		$this->assertEquals(actual: $date->format(), expected: "Mon Jan 1 2024");
	}
}
