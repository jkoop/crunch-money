<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	@include('blocks.head')
</head>

<body>
	@include('blocks.admin-navbar')
	@include('blocks.errors')
	<main>
		@yield('content')
	</main>
	@include('blocks.footer')
</body>

</html>
