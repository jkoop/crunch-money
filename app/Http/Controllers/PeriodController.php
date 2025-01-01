<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class PeriodController extends Controller {
	public function set(Request $request) {
		$request->validate([
			"period_id" => "required|exists:periods,id",
		]);

		$period = Period::find($request->period_id);
		Session::put("period_id", $period->id);

		return redirect()->back();
	}
}
