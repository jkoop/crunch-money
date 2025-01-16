<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table("periods", function (Blueprint $table) {
			$table->unique(["owner_id", "end"]);
		});
	}

	public function down(): void {
		Schema::table("periods", function (Blueprint $table) {
			$table->dropUnique(["owner_id", "end"]);
		});
	}
};
