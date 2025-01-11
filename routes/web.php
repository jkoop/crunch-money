<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\FundsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
	Route::redirect("/", "/b")->name("dashboard");

	Route::get("/b", [BudgetsController::class, "get"])->name("budgets");
	Route::get("/b/{slug}", [BudgetController::class, "get"])->name("budgets.get");
	Route::post("/b/{slug}", [BudgetController::class, "post"])->name("budgets.post");

	Route::get("/f", [FundsController::class, "get"])->name("funds");
	Route::get("/f/_balances", [FundController::class, "balances"])->name("funds.balances");
	Route::get("/f/{slug}", [FundController::class, "get"])->name("funds.get");
	Route::post("/f/{slug}", [FundController::class, "post"])->name("funds.post");

	Route::get("/t", [TransactionsController::class, "get"])->name("transactions");
	Route::post("/t", [TransactionsController::class, "post"])->name("transactions.post");

	Route::get("/p", [PeriodsController::class, "get"])->name("periods");
	Route::get("/p/_carryover", [PeriodController::class, "carryover"])->name("periods.carryover");
	Route::get("/p/{start_date}", [PeriodController::class, "get"])->name("periods.get");
	Route::post("/p/{start_date}", [PeriodController::class, "post"])->name("periods.post");

	Route::get("/profile", [ProfileController::class, "get"])->name("profile");
	Route::post("/profile", [ProfileController::class, "post"])->name("profile.post");

	Route::get("/logout", [LoginController::class, "logout"])->name("logout");

	Route::post("/set-period", [PeriodsController::class, "set"])->name("set-period");

	Route::middleware("can:admin")->group(function () {
		Route::get("/a", [AdminDashboardController::class, "get"])->name("admin");

		Route::get("/u", [UsersController::class, "get"])->name("users");
		Route::get("/u/{userId}", [UserController::class, "get"])->name("users.get");
		Route::post("/u/{userId}", [UserController::class, "post"])->name("users.post");
	});
});

Route::middleware("guest")->group(function () {
	Route::get("/login", [LoginController::class, "get"])->name("login");
	Route::post("/login", [LoginController::class, "post"]);
});

Route::delete("/downloads/{id}", [SessionController::class, "deleteDownload"])->name("downloads.delete");
