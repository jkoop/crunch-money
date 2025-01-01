@extends('layouts.default')
@section('title', $title)

@section('content')
	<form action="{{ route('funds.post', ['slug' => $fund?->slug ?? 'new']) }}" method="post">
		@csrf
		<label for="name">Name</label>
		<input name="name" type="text" value="{{ old('name', $fund?->name) }}" required maxlength="255" />
		<button type="submit">Save</button>
	</form>
@endsection
