<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>@yield('title') - Admin - Crunch Money</title>

	@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
	<h1>@yield('title')</h1>

	@include('blocks.admin-navbar')
	@include('blocks.errors')
	@yield('content')
	@include('blocks.footer')
</body>

</html>
