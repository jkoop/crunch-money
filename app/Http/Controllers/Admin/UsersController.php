<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

final class UsersController extends Controller {
	public function get() {
		return view("users.list");
	}
}
