<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use Illuminate\Http\Request;

final class FundsController extends Controller {
	public function get() {
		return view("funds", [
			"funds" => Fund::all(),
		]);
	}
}
