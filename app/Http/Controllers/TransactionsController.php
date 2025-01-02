<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class TransactionsController extends Controller {
	public function get() {
		return view("transactions.list", [
			"transactions" => Transaction::orderByDesc("date")->orderBy("id", "desc")->get(),
		]);
	}

	public function post(Request $request) {
		$request->validate([
			"budget_id" => "required|exists:budgets,id",
			"date" => "required|date",
			"amount" => "required|numeric",
			"description" => "required|string|max:255",
			"negate" => "nullable|boolean",
		]);

		$negate = $request->negate ?? false;

		Transaction::create([
			"owner_id" => Auth::id(),
			"budget_id" => $request->budget_id,
			"period_id" => Period::current()->id,
			"date" => $request->date,
			"amount" => $negate ? -$request->amount : $request->amount,
			"description" => $request->description,
		]);
		return redirect()->back();
	}
}
