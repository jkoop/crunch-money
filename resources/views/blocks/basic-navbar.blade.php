@if (Auth::check())
	<div>
		<a href="{{ route('budgets') }}">Budgets</a> -
		<a href="{{ route('funds') }}">Funds</a> -
		<a href="{{ route('transactions') }}">Transactions</a> -
		<a href="{{ route('periods') }}">Periods</a> -
		<a href="{{ route('profile') }}">Profile</a>
		@can('admin')
			- <a class="float-right" href="{{ route('admin') }}">Admin</a>
		@endcan
	</div>
	<div>
		<x-period-picker />
	</div>
@endif
