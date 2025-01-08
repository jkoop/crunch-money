<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;

final class ProfileController extends Controller {
	public function get() {
		return view("profile", [
			"user" => Auth::user(),
		]);
	}

	public function post(Request $request) {
		$request->validate([
			"name" => "required|string|max:255",
		]);

		$user = Auth::user();
		$user->name = $request->name;

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
