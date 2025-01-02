@extends('layouts.default')
@section('title', $budget->name . ' - Budgets')

@section('content')
	<form action="{{ route('budgets.post', $budget->slug) }}" method="post">
		@csrf
		<label for="name">Name</label>
		<input name="name" type="text" value="{{ old('name', $budget->name) }}" required maxlength="255" />
		<button type="submit">Save</button>
	</form>
@endsection
