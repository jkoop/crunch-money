<?php

namespace App\Providers;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Register any application services.
	 */
	public function register(): void {
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void {
		// add regexp function to SQLite
		if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
			DB::connection()
				->getPdo()
				->sqliteCreateFunction("regexp", function ($pattern, $value) {
					mb_regex_encoding("UTF-8");
					return false !== mb_ereg($pattern, $value) ? 1 : 0;
				});
		}

		Blade::directive("money", function ($expression) {
			return "<?php echo number_format($expression, 2, '.', ','); ?>";
		});

		Gate::define("admin", function (User $user) {
			return $user->is_admin;
		});

		Gate::define("edit-profile", function (User $user) {
			return $user->type != UserType::Demo;
		});
	}
}
