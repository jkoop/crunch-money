<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\FundsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
	Route::get("/", [DashboardController::class, "get"])->name("dashboard");

	Route::get("/f", [FundsController::class, "get"])->name("funds");
	Route::get("/f/new", [FundController::class, "new"])->name("funds.new");
	Route::get("/f/{slug}", [FundController::class, "get"])->name("funds.get");
	Route::post("/f/{slug}", [FundController::class, "post"])->name("funds.post");

	Route::get("/t", [TransactionsController::class, "get"])->name("transactions");

	Route::get("/logout", [LoginController::class, "logout"])->name("logout");
});

Route::get("/login", [LoginController::class, "get"]);
Route::post("/login", [LoginController::class, "post"])->name("login");
