@extends('layouts.default')
@section('title', 'Login')

@section('content')
	<form class="flex w-full justify-center" action="{{ route('login') }}" method="post">
		@csrf
		<fieldset>
			<legend>Login</legend>
			<label for="token">Token</label>
			<input name="token" type="password" autofocus required>
			<button type="submit">Login</button>
		</fieldset>
	</form>

	@if ($demoUsers->isNotEmpty())
		<div class="flex w-full justify-center">
			<div>
				@if ($demoUsers->count() == 1)
					<p class="font-bold">Demo user token:</p>
				@else
					<p class="font-bold">Demo user tokens:</p>
				@endif

				<ul>
					@foreach ($demoUsers as $demoUser)
						<li><code>{{ $demoUser->token }}</code></li>
					@endforeach
				</ul>

				<p class="mt-4 text-gray-400">
					Demo users are reset daily
				</p>
			</div>
		</div>
	@endif
@endsection
