<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create("fund_period", function (Blueprint $table) {
			$table->foreignId("fund_id")->constrained("funds")->cascadeOnDelete()->onUpdateRestrict();
			$table->foreignId("period_id")->constrained("periods")->cascadeOnDelete()->onUpdateRestrict();
			$table->unique(["fund_id", "period_id"]);
		});
	}

	public function down(): void {
		Schema::dropIfExists("fund_period");
	}
};
