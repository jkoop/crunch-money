<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create("funds", function ($table) {
			$table->id();
			$table->foreignId("owner_id")->constrained("users");
			$table->string("name");
			$table->string("slug");
			$table->timestamps();
			$table->softDeletes();
			$table->unique(["owner_id", "slug"]);
		});
	}

	public function down(): void {
		Schema::dropIfExists("funds");
	}
};
