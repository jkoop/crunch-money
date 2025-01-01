<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

final class DashboardController extends Controller {
	public function get() {
		return view("dashboard");
	}
}
