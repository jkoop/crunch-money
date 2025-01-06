<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table("budgets", function (Blueprint $table) {
			$table->string("amount")->after("name")->nullable();
		});

		// Get sum of transactions for each budget
		$budgets = DB::table("budgets")
			->select("budgets.id", DB::raw("transactions.amount as amount"))
			->join("transactions", "budgets.id", "=", "transactions.budget_id")
			->where("transactions.is_system", true)
			->groupBy("budgets.id");

		// Update each budget's amount with the sum
		foreach ($budgets->get() as $budget) {
			DB::table("budgets")
				->where("id", $budget->id)
				->update(["amount" => $budget->amount]);
		}

		DB::table("budgets")
			->where("amount", null)
			->update(["amount" => "0"]);

		Schema::table("budgets", function (Blueprint $table) {
			$table->string("amount")->nullable(false)->change();
		});

		Schema::table("fund_period", function (Blueprint $table) {
			$table->string("amount")->after("name")->nullable();
		});

		// Get sum of transactions for each fund
		$funds = DB::table("funds")
			->select("funds.id", DB::raw("transactions.amount as amount"))
			->join("transactions", "funds.id", "=", "transactions.fund_id")
			->where("transactions.is_system", true)
			->groupBy("funds.id");

		// Update each fund's amount with the sum
		foreach ($funds->get() as $fund) {
			DB::table("fund_period")
				->where("id", $fund->id)
				->update(["amount" => $fund->amount]);
		}

		DB::table("fund_period")
			->where("amount", null)
			->update(["amount" => "0"]);

		Schema::table("fund_period", function (Blueprint $table) {
			$table->string("amount")->nullable(false)->change();
		});
	}

	public function down(): void {
		Schema::table("budgets", function (Blueprint $table) {
			$table->dropColumn("amount");
		});

		Schema::table("fund_period", function (Blueprint $table) {
			$table->dropColumn("amount");
		});
	}
};
