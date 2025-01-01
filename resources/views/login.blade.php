@extends('layouts.default')
@section('title', 'Login')

@section('content')
	<form action="{{ route('login') }}" method="post">
		@csrf
		<label for="token">Token</label>
		<input name="token" type="password" autofocus required>
		<button type="submit">Login</button>
	</form>
@endsection
