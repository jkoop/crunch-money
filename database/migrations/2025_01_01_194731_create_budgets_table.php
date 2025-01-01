<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create("budgets", function (Blueprint $table) {
			$table->id();
			$table->foreignId("owner_id")->constrained("users");
			$table->foreignId("period_id")->constrained("periods");
			$table->string("name");
			$table->string("slug")->unique();
			$table->timestamps();
		});
	}

	public function down(): void {
		Schema::dropIfExists("budgets");
	}
};
