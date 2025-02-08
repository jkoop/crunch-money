<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class Init extends Command {
	protected $signature = "app:init {--f|force}";
	protected $description = "Seed the database with default data to start you off";

	public function handle() {
		if (User::where("is_admin", 1)->exists() && !$this->option("force")) {
			$this->info("There is already an admin. Not creating one.");
			return;
		}

		$user = new User();
		$user->name = "1st User";
		$user->is_admin = true;
		$user->regenerateToken();
		$user->save();

		$this->info("\n\n\tYour login token is $user->token\n\n\n");
	}
}
