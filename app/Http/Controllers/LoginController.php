<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

final class LoginController extends Controller {
	public function get() {
		return view("login");
	}

	public function post(Request $request) {
		$request->validate([
			"token" => "required|string",
		]);

		$user = User::where("token", $request->token)->first();

		if ($user == null) {
			return Redirect::to("/login")->withErrors(["token" => "Invalid token"]);
		}

		Auth::login($user);

		// if (Session::get("intended"))
		if (Session::get("url.intended") != URL::to("/login") && Session::get("url.intended") != URL::to("/logout")) {
			return Redirect::intended("/b");
		} else {
			return Redirect::to("/b");
		}
	}

	public function logout() {
		Auth::logout();
		Session::invalidate();
		return Redirect::to("/login");
	}
}
