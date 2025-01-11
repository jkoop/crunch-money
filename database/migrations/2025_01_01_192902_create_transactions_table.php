<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create("transactions", function (Blueprint $table) {
			$table->id();
			$table->foreignId("owner_id")->constrained("users")->cascadeOnDelete()->onUpdateRestrict();
			$table->foreignId("fund_id")->nullable()->constrained("funds")->cascadeOnDelete()->onUpdateRestrict();
			$table->foreignId("budget_id")->nullable()->constrained("budgets")->cascadeOnDelete()->onUpdateRestrict();
			$table->foreignId("period_id")->constrained("periods")->cascadeOnDelete()->onUpdateRestrict();
			$table->string("description");
			$table->date("date");
			$table->decimal("amount", 10, 2);
			$table->boolean("is_system")->default(false);
			$table->timestamps();
		});
	}

	public function down(): void {
		Schema::dropIfExists("transactions");
	}
};
