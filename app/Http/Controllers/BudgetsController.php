<?php

namespace App\Http\Controllers;

use App\Models\Budget;

final class BudgetsController extends Controller {
	public function get() {
		return view("budgets.list", [
			"budgets" => Budget::orderBy("name")->with("transactions")->get(),
		]);
	}
}
