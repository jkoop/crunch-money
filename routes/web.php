<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundsController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
	Route::get("/", [DashboardController::class, "get"])->name("dashboard");

	Route::get("/f", [FundsController::class, "get"])->name("funds");

	Route::get("/logout", [LoginController::class, "logout"])->name("logout");
});

Route::get("/login", [LoginController::class, "get"]);
Route::post("/login", [LoginController::class, "post"])->name("login");
