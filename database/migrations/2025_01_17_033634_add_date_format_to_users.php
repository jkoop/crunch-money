<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table("users", function (Blueprint $table) {
			$table->enum("date_format", ["ymd", "mdy", "dmy"])->default("mdy");
			$table->boolean("two_digit_year")->default(false);
			$table->boolean("show_dow_on_tables")->default(true);
			$table->boolean("show_dow_on_period_picker")->default(false);
			$table->boolean("always_show_year")->default(false);
		});
	}

	public function down(): void {
		Schema::table("users", function (Blueprint $table) {
			$table->dropColumn([
				"date_format",
				"two_digit_year",
				"show_dow_on_tables",
				"show_dow_on_period_picker",
				"always_show_year",
			]);
		});
	}
};
