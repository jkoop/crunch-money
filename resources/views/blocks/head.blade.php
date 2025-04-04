<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="theme-color" content="#374151" />

<title>@yield('title') - {{ config('app.name') }}</title>

<link type="image/svg+xml" href="/favicon.svg" rel="icon" />

@if (Session::get('downloads', []) != [])
	<script>
		window.downloads = {{ Js::from(Session::get('downloads')) }};
	</script>
@endif

@if (config("app.tracking.html", null) != null)
	{!! config("app.tracking.html", null) !!}
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])
