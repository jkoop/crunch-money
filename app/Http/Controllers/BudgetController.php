<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
final class BudgetController extends Controller {
	public function get(string $slug) {
		$budget = Budget::where("slug", $slug)->firstOrFail();

		return view("budgets.edit", compact("budget"));
	}

	public function post(Request $request, string $slug) {
		$budget = Budget::where("slug", $slug)->firstOrFail();

		$request->validate([
			"name" => "required|string|max:255",
		]);

		$slug = Str::slug($request->name);
		if ($slug == "new") {
			return back()->with("error", "Budget name cannot be like 'new'");
		}
		if ($slug == "") {
			return back()->with("error", "Budget name cannot be empty");
		}

		$slug = Str::slug($budget["name"]);
		$counter = "";
		while (true) {
			try {
				if ($budget != null) {
					$budget->update([
						"name" => $request->name,
						"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
					]);
				} else {
					Budget::create([
						"owner_id" => Auth::id(),
						"name" => $request->name,
						"slug" => $slug . $counter, // this is too clever: the counter is negative, causing a dash in the slug
					]);
				}
				break;
			} catch (UniqueConstraintViolationException $e) {
				if (!Str::of($e)->contains("UNIQUE constraint failed: budgets.owner_id, budgets.slug")) {
					throw $e;
				}
				if ($counter == "") {
					$counter = -1;
				}
				$counter--;
			}
		}

		return redirect()->route("budgets");
	}
}
