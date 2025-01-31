<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Fund;
use App\Models\Transaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TransactionsTest extends TestCase {
	#[Test]
	public function transactionsListWorks(): void {
		$budget = Budget::factory()->for($this->user, "owner")->for($this->period)->create();
		$fund = Fund::factory()->for($this->user, "owner")->create();
		$transactions = Transaction::factory()
			->count(5)
			->for($this->user, "owner")
			->for($this->period)
			->for($budget)
			->create(["date" => "2020-01-05"])
			->concat(
				Transaction::factory()
					->count(5)
					->for($this->user, "owner")
					->for($this->period)
					->for($fund)
					->create(["date" => "2020-01-03"]),
			);

		$this->get("/t")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSee($budget->name)
			->assertSeeTextInOrder(
				$transactions
					->sortByDesc(["date", "id"])
					->map(
						fn(Transaction $transaction) => [
							$transaction->date->format(),
							number_format($transaction->amount, 2, ".", ","),
							$transaction->description,
						],
					)
					->flatten()
					->toArray(),
			);
	}

	#[Test]
	public function createTransactionForBudget(): void {
		$budget = Budget::factory()->for($this->user, "owner")->for($this->period)->create();

		$this->assertFalse(Transaction::exists());

		$this->post("/t", [
			"budget_id" => $budget->id,
			"date" => "2020-01-05",
			"amount" => 123.45,
			"description" => "armadillo goldfish driving",
		])
			->assertSessionHasNoErrors()
			->assertRedirect();

		$this->assertTrue(Transaction::exists());
		$transaction = Transaction::first();

		$this->get("/t")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSeeTextInOrder([
				$transaction->date->format(),
				number_format($transaction->amount, 2, ".", ","),
				$transaction->description,
			]);
	}
}
