<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>@yield('title') - Crunch Money</title>

	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
	<h1>@yield('title')</h1>

	@if (Auth::check())
		<div>
			<a href="{{ route('budgets') }}">Budgets</a> -
			<a href="{{ route('funds') }}">Funds</a> -
			<a href="{{ route('transactions') }}">Transactions</a> -
			<a href="{{ route('periods') }}">Periods</a>
		</div>
		<div>
			<x-period-picker />
		</div>
	@endif

	@if (session('error') || $errors->any())
		<div class="mb-4 bg-red-500 p-2 text-white">
			{{ session('error') }}
			@foreach ($errors->all() as $error)
				{{ $error }}
			@endforeach
		</div>
	@endif

	@yield('content')

	<hr>
	{{ Auth::user()?->name ?? 'Guest' }}
	@if (Auth::check())
		- <a href="{{ route('logout') }}">Logout</a>
	@endif
</body>

</html>
