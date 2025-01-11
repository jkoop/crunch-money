<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create("incomes", function (Blueprint $table) {
			$table->id();
			$table->foreignId("owner_id")->constrained("users")->cascadeOnDelete()->onUpdateRestrict();
			$table->foreignId("period_id")->constrained("periods")->cascadeOnDelete()->onUpdateRestrict();
			$table->string("name");
			$table->decimal("amount", 10, 2);
			$table->timestamps();
		});
	}

	public function down(): void {
		Schema::dropIfExists("incomes");
	}
};
