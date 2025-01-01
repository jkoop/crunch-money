<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller {
	public function get() {
		return view("login");
	}

	public function post(Request $request) {
		$request->validate([
			"token" => "required|string",
		]);

		$user = User::where("token", $request->token)->first();

		if ($user == null) {
			return redirect()
				->route("login")
				->withErrors(["Invalid token"]);
		}

		Auth::login($user);
		return redirect()->route("dashboard");
	}
}
