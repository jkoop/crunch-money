<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

final class TransactionsController extends Controller {
	public function get() {
		return view("transactions.list", [
			"transactions" => Transaction::orderByDesc("date")->get(),
		]);
	}
}
