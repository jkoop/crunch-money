@if (Auth::check())
	<nav>
		<div id="period-picker-container">
			@if (Str::of(Request::path())->match('#^p/\d#')->isEmpty())
				<a class="text-sm" href="/p/{{ Period::current()->start->format('Y-m-d') }}">Edit</a>
				<x-period-picker />
			@endif
		</div>
		<div>
			<a href="{{ route('budgets') }}">Budgets</a>
			<a href="{{ route('funds') }}">Funds</a>
			<a href="{{ route('transactions') }}">Transactions</a>
			<a href="{{ route('periods') }}">Periods</a>

			<span class="flex-grow" id="put-the-period-picker-after-me"></span>
			<span class="flex-grow"></span>

			<a href="{{ route('profile') }}">Profile</a>
			@can('admin')
				<a href="{{ route('admin') }}">Admin</a>
			@endcan
		</div>
	</nav>
@endif
