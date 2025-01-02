<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
final class BudgetController extends Controller {
	public function get(string $slug) {
		$budget = Budget::where("slug", $slug)->firstOrFail();

		return view("budgets.edit", compact("budget"));
	}

	public function post(Request $request, string $slug) {
		$budget = Budget::where("slug", $slug)->first();
		if ($budget == null and $slug != "new") {
			abort(404);
		}

		$request->validate([
			"name" => "required|string|max:255",
		]);

		$slug = Str::slug($request->name);
		if (Budget::where("slug", $slug)->exists()) {
			return back()->with("error", "Budget with this name already exists");
		}
		if ($slug == "new") {
			return back()->with("error", "Budget name cannot be like 'new'");
		}
		if ($slug == "") {
			return back()->with("error", "Budget name cannot be empty");
		}

		if ($budget != null) {
			$budget->update([
				"name" => $request->name,
				"slug" => $slug,
			]);
		} else {
			Budget::create([
				"owner_id" => Auth::id(),
				"name" => $request->name,
				"slug" => $slug,
			]);
		}

		return redirect()->route("budgets");
	}
}
