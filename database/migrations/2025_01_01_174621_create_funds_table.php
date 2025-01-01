<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		// this is raw SQL instead of using the schema builder because schema builder doesn't support the check constraint on SQLite
		DB::statement(
			<<<SQL
			    CREATE TABLE funds (
			        id INTEGER PRIMARY KEY AUTOINCREMENT,
			        owner_id INTEGER NOT NULL,
			        name VARCHAR NOT NULL,
			        slug VARCHAR NOT NULL,
			        created_at TIMESTAMP NULL,
			        updated_at TIMESTAMP NULL,
			        deleted_at TIMESTAMP NULL,
			        FOREIGN KEY (owner_id) REFERENCES users(id),
			        UNIQUE (owner_id, slug),
			        CHECK (slug REGEXP '^[a-z0-9-]+$'
			            AND slug NOT LIKE '-%'
			            AND slug NOT LIKE '%--%'
			            AND slug NOT LIKE '%-'
			            AND slug != 'new')
			    )
			SQL
			,
		);
	}

	public function down(): void {
		Schema::dropIfExists("funds");
	}
};
