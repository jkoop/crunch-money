<?php

namespace App\View\Components;

use App\Models\Period;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class PeriodPicker extends Component {
	public function __construct() {
	}

	public function render(): View|Closure|string {
		$currentPeriod = Period::current(); // this is called first to create the default period
		$periods = Period::orderBy("start", "desc")->get();
		return view("components.period-picker", compact("currentPeriod", "periods"));
	}
}
