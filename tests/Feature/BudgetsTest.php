<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Transaction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BudgetsTest extends TestCase {
	#[Test]
	public function budgetsListWorks(): void {
		$budgets = Budget::factory()->count(4)->for($this->user, "owner")->for($this->period)->create();
		Transaction::factory()
			->count(5)
			->for($this->user, "owner")
			->for($this->period)
			->for($budgets[0])
			->create(["date" => "2020-01-05"]);

		$this->get("/b")
			->assertSessionHasNoErrors()
			->assertOk()
			->assertSeeTextInOrder(
				$budgets
					->sortBy("name")
					->map(
						fn(Budget $budget) => [
							$budget->name,
							number_format($budget->transactions->pluck("amount")->sum(), 2, ".", ","),
						],
					)
					->flatten()
					->toArray(),
			);
	}

	#[Test]
	public function budgetTransactionsListWorks(): void {
		$budget = Budget::factory()->for($this->user, "owner")->for($this->period)->create();
		$transactions = Transaction::factory()
			->count(5)
			->for($this->user, "owner")
			->for($this->period)
			->for($budget)
			->create(["date" => "2020-01-05"]);

		$this->get("/b/" . $budget->slug)
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
}
