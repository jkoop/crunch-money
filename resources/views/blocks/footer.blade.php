<footer>
	{{ Auth::user()->name ?? 'Guest' }}

	@if (Auth::check())
		<a href="{{ route('logout') }}">Logout</a>
	@endif

	<a href="https://github.com/jkoop/crunch-money/issues" target="_blank">Report a bug</a>

	@if (config('app.service_status.url'))
		<a href="{{ config('app.service_status.url') }}" target="_blank">Service status</a>
	@endif
</footer>
