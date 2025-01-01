<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
	Route::get("/dashboard", [DashboardController::class, "get"])->name("dashboard");
});

Route::get("/login", [LoginController::class, "get"]);
Route::post("/login", [LoginController::class, "post"])->name("login");
