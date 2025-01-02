<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\FundsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
	Route::redirect("/", "/b")->name("dashboard");

	Route::get("/b", [BudgetsController::class, "get"])->name("budgets");
	Route::get("/b/{slug}", [BudgetController::class, "get"])->name("budgets.get");
	Route::post("/b/{slug}", [BudgetController::class, "post"])->name("budgets.post");

	Route::get("/f", [FundsController::class, "get"])->name("funds");
	Route::get("/f/_balances", [FundController::class, "balances"])->name("funds.balances");
	Route::get("/f/new", [FundController::class, "new"])->name("funds.new");
	Route::get("/f/{slug}", [FundController::class, "get"])->name("funds.get");
	Route::post("/f/{slug}", [FundController::class, "post"])->name("funds.post");

	Route::get("/t", [TransactionsController::class, "get"])->name("transactions");
	Route::post("/t", [TransactionsController::class, "post"])->name("transactions.post");

	Route::get("/p", [PeriodsController::class, "get"])->name("periods");
	Route::get("/p/new", [PeriodController::class, "get"])->name("periods.new");
	Route::get("/p/{start_date}", [PeriodController::class, "get"])->name("periods.get");
	Route::post("/p/{start_date}", [PeriodController::class, "post"])->name("periods.post");

	Route::post("/set-period", [PeriodsController::class, "set"])->name("set-period");
	Route::get("/logout", [LoginController::class, "logout"])->name("logout");
});

Route::middleware("guest")->group(function () {
	Route::get("/login", [LoginController::class, "get"])->name("login");
	Route::post("/login", [LoginController::class, "post"]);
});
