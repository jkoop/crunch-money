<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class PeriodsController extends Controller {
	public function set(Request $request) {
		$request->validate([
			"period_id" => "required|exists:periods,id",
		]);

		$period = Period::find($request->period_id);
		Session::put("period_id", $period->id);

		return redirect()->back();
	}

	public function get() {
		Period::current(); // just to make sure the current period is created

		return view("periods.list", [
			"periods" => Period::orderBy("start", "desc")->get(),
		]);
	}
}
