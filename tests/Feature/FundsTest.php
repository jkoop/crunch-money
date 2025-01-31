<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\Transaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FundsTest extends TestCase {
	#[Test]
	public function fundsListWorks(): void {
		$funds = Fund::factory()->count(4)->for($this->user, "owner")->create();
		Transaction::factory()
			->count(5)
			->for($this->user, "owner")
			->for($this->period)
			->for($funds[0])
			->create(["date" => "2020-01-05"]);

		// associate funds with the period
		$funds->each(function (Fund $fund): void {
			$fund->periods()->attach($this->period, ["amount" => "not used during this test"]);
		});

		$this->get("/f")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSeeTextInOrder(
				$funds
					->sortBy("name")
					->map(
						fn(Fund $fund) => [
							$fund->name,
							number_format($fund->transactions->pluck("amount")->sum(), 2, ".", ","),
						],
					)
					->flatten()
					->toArray(),
			);
	}

	#[Test]
	public function budgetTransactionsListWorks(): void {
		$fund = Fund::factory()->for($this->user, "owner")->create();
		$transactions = Transaction::factory()
			->count(5)
			->for($this->user, "owner")
			->for($this->period)
			->for($fund)
			->create(["date" => "2020-01-05"]);

		// associate fund with the period
		$fund->periods()->attach($this->period, ["amount" => "not used during this test"]);

		$this->get("/f/" . $fund->slug)
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSee($fund->name)
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
}
