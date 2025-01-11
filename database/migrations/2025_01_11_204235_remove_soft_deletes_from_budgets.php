<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		DB::table("budgets")->whereNotNull("deleted_at")->delete();
		Schema::table("budgets", function (Blueprint $table) {
			$table->dropSoftDeletes();
		});
	}

	public function down(): void {
		Schema::table("budgets", function (Blueprint $table) {
			$table->softDeletes();
		});
	}
};
