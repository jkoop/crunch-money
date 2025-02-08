<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Fund;
use App\Models\Income;
use App\Models\Period;
use Illuminate\Support\Js;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PeriodTest extends TestCase {
	#[Test]
	public function listIsSorted(): void {
		Period::factory()
			->for($this->user, "owner")
			->create(["start" => "2019-12-01", "end" => "2019-12-31"]);
		Period::factory()
			->for($this->user, "owner")
			->create(["start" => "2020-02-01", "end" => "2020-02-29"]);

		$this->get("/p")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSeeTextInOrder(["Feb 1 2020", "Jan 1 2020", "Dec 1 2019"]);
	}

	#[Test]
	public function editPageHasInfo(): void {
		$incomes = Income::factory()->count(3)->for($this->period)->for($this->user, "owner")->create();

		$budgets = Budget::factory()->count(3)->for($this->period)->for($this->user, "owner")->create();

		$funds = Fund::factory()->count(3)->for($this->user, "owner")->create();

		$funds->each(
			fn(Fund $fund) => $fund->periods()->attach($this->period, ["amount" => fake()->randomFloat(2, 0, 9999.99)]),
		);

		$this->get("/p/2020-01-01")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSee([
				Js::from("2020-01-01"),
				Js::from("2020-01-31"),
				Js::from(
					$incomes->sortBy("name")->values()->map(
						fn(Income $income) => [
							"id" => $income->id,
							"name" => $income->name,
							"amount" => $income->amount,
						],
					),
				),
				Js::from(
					$budgets->sortBy("name")->values()->map(
						fn(Budget $budget) => [
							"id" => $budget->id,
							"name" => $budget->name,
							"amount" => $budget->amount,
						],
					),
				),
				Js::from(
					$this->period->funds->sortBy("name")->values()->map(
						fn(Fund $fund) => [
							"id" => $fund->id,
							"name" => $fund->name,
							"amount" => $fund->pivot->amount,
						],
					),
				),
			]);
	}

	// TODO: test saving a period

	#[Test]
	public function deletePeriod(): void {
		$this->assertTrue(Period::where("start", "2020-01-01")->exists());

		$this->post("/p/2020-01-01", [
			"delete" => "on",
		])
			->assertSessionHasNoErrors()
			->assertRedirect();

		$this->assertFalse(Period::where("start", "2020-01-01")->exists());
	}
}
