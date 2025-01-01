<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

final class BudgetsController extends Controller {
	public function get() {
		return view("budgets.list", [
			"budgets" => Budget::orderBy("name")->get(),
		]);
	}
}
