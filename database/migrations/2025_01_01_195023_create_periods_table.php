<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create("periods", function (Blueprint $table) {
			$table->id();
			$table->foreignId("owner_id")->constrained("users");
			$table->date("start");
			$table->date("end");
			$table->unique(["owner_id", "start"]);
			$table->timestamps();
		});
	}

	public function down(): void {
		Schema::dropIfExists("periods");
	}
};