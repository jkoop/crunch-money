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

	@if ($errors->any())
		<div class="mb-4 rounded-md bg-red-500 p-2 text-white">
			@foreach ($errors->all() as $error)
				{{ $error }}
			@endforeach
		</div>
	@endif

	@yield('content')
</body>

</html>
