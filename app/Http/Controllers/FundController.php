<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class FundController extends Controller {
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

	public function balances(Request $request): Response {
		$request->validate([
			"date" => "required|date",
		]);

		$funds = Fund::withTrashed()
			->with("transactions", function (HasMany $builder) use ($request): void {
				$builder->where("date", "<", $request->date);
			})
			->get();

		return response(
			$funds->mapWithKeys(
				fn(Fund $fund) => [
					$fund->id => $fund->transactions->sum("amount"),
				],
			),
		)->header("Cache-Control", "private, max-age=5");
	}

	public function post(Request $request, string $slug) {
		$fund = Fund::where("slug", $slug)->firstOrFail();

		$request->validate([
			"name" => "required|string|max:255",
		]);

		$slug = Str::slug($request->name);
		if (Fund::where("slug", $slug)->exists()) {
			return back()->with("error", "Fund with this name already exists");
		}
		if ($slug == "new") {
			return back()->with("error", "Fund name cannot be like 'new'");
		}
		if ($slug == "") {
			return back()->with("error", "Fund name cannot be empty");
		}

		$fund->update([
			"name" => $request->name,
			"slug" => $slug,
		]);

		return redirect()->route("funds");
	}
}
