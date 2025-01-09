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
@endsection
