<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table("budgets", function (Blueprint $table) {
			$table->dropUnique(["owner_id", "slug"]);
			$table->unique(["owner_id", "period_id", "slug"]);
		});
	}

	public function down(): void {
		Schema::table("budgets", function (Blueprint $table) {
			$table->dropUnique(["owner_id", "period_id", "slug"]);
			$table->unique(["owner_id", "slug"]);
		});
	}
};
