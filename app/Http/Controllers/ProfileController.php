<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;

final class ProfileController extends Controller {
	public function get() {
		if (!Gate::allows("edit-profile")) {
			Session::push("warnings", "You are not allowed to edit your profile.");
		}

		return view("profile", [
			"user" => Auth::user(),
		]);
	}

	public function post(Request $request) {
		$request->validate([
			"name" => "required|string|max:255",
			"date_format" => "required|string|in:ymd,mdy,dmy",
		]);

		$user = Auth::user();
		$user->name = $request->name;
		$user->date_format = $request->date_format;
		$user->two_digit_year = $request->has("two_digit_year");
		$user->show_dow_on_tables = $request->has("show_dow_on_tables");
		$user->show_dow_on_period_picker = $request->has("show_dow_on_period_picker");
		$user->always_show_year = $request->has("always_show_year");

		if ($request->has("regenerate_token")) {
			$user->regenerateToken();
			$user->save();

			Session::regenerate();
			Session::flash(
				"success",
				new HtmlString(
					"Token regenerated. New token: <code>" . e($user->token) . "</code>. IT WILL NEVER BE SHOWN AGAIN.",
				),
			);

			// flashed session data isn't available after a redirect
			return Redirect::to("/login");
		}

		$user->save();
		return Redirect::back();
	}
}
