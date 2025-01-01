<?php

namespace App\Http\Controllers;

use App\Models\Fund;

final class FundsController extends Controller {
	public function get() {
		return view("funds.list", [
			"funds" => Fund::orderBy("name")->get(),
		]);
	}
}
