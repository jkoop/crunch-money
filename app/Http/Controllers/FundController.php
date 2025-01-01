<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class FundController extends Controller {
	public function new() {
		return view("funds.edit", [
			"title" => "New fund",
			"fund" => null,
		]);
	}

	public function get(string $slug) {
		$fund = Fund::where("slug", $slug)->first();
		if (!$fund) {
			abort(404);
		}

		return view("funds.edit", [
			"title" => $fund->name . " - Funds",
			"fund" => $fund,
		]);
	}

	public function post(Request $request, string $slug) {
		$fund = Fund::where("slug", $slug)->first();
		if ($fund == null and $slug != "new") {
			abort(404);
		}

		$request->validate([
			"name" => "required|string|max:255",
		]);

		$slug = Str::slug($request->name);
		if (Fund::where("slug", $slug)->exists()) {
			return redirect()->route("funds.new")->with("error", "Fund with this name already exists");
		}
		if ($slug == "new") {
			return redirect()->route("funds.new")->with("error", "Fund name cannot be like 'new'");
		}
		if ($slug == "") {
			return redirect()->route("funds.new")->with("error", "Fund name cannot be empty");
		}

		if ($fund != null) {
			$fund->update([
				"name" => $request->name,
				"slug" => $slug,
			]);
		} else {
			Fund::create([
				"owner_id" => Auth::id(),
				"name" => $request->name,
				"slug" => $slug,
			]);
		}

		return redirect()->route("funds");
	}
}
