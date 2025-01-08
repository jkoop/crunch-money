<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ImpossibleStateException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\HtmlString;

final class UserController extends Controller {
	public function get(string $userId) {
		if ($userId == "new") {
			$user = new User(["name" => "New User"]);
		} else {
			$user = User::findOrFail($userId);
		}

		return view("users.edit", compact("user"));
	}

	public function post(Request $request, string $userId) {
		$request->validate([
			"name" => "required|string|max:255",
			"notes" => "nullable|string|max:65535",
			"type" => "required|string|in:admin,basic",
		]);

		if ($userId == "new") {
			$user = new User();
		} else {
			$user = User::findOrFail($userId);
		}

		if ($request->has("delete")) {
			$user->delete();
			return Redirect::to("/u");
		}

		$user->name = $request->name;
		$user->notes = $request->notes;

		switch ($request->input("type")) {
			case "admin":
				$user->is_admin = true;
				break;
			case "basic":
				$user->is_admin = false;
				break;
			default:
				throw new ImpossibleStateException();
		}

		if ($request->has("regenerate_token") || $user->id == null) {
			$user->regenerateToken();

			if (Auth::user()->id == $user->id) {
				$user->save();
				Session::regenerate();
				Session::flash(
					"success",
					new HtmlString(
						"Token regenerated. New token: <code>" .
							e($user->token) .
							"</code>. It will never be shown again.",
					),
				);

				// flashed session data isn't available after a redirect
				return Redirect::to("/login");
			} else {
				Session::flash(
					"success",
					new HtmlString(
						"Token regenerated. New token: <code>" .
							e($user->token) .
							"</code>. It will never be shown again.",
					),
				);
			}
		}

		$user->save();
		return Redirect::to("/u");
	}
}
