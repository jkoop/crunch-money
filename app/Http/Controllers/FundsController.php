<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Scopes\PeriodScope;
use Illuminate\Http\Request;

final class FundsController extends Controller {
	public function get(Request $request) {
		$funds = Fund::orderBy("name");
		$showAll = $request->has("all");

		if ($showAll) {
			$funds = $funds->withoutGlobalScope(PeriodScope::class)->get();
		} else {
			$funds = $funds->get();
		}

		return view("funds.list", compact("funds", "showAll"));
	}
}
