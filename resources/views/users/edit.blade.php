@extends('layouts.admin')
@section('title', $user->name . ' - Users')

@section('content')
	<form method="post">
		@csrf
		<label class="mb-4 block">
			Name
			<input name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" />
		</label>

		<label class="block">
			<input name="regenerate_token" type="checkbox" @checked($user->id == null) @disabled($user->id == null) />
			Regenerate Token
		</label>
		<p class="mb-4 text-sm text-gray-400">This will log out the user of all devices.</p>

		<label>
			Notes<br>
			<textarea name="notes" maxlength="65535">{{ $user->notes }}</textarea>
		</label>
		<p class="mb-4 text-sm text-gray-400">Not shown to the user; for your convenience.</p>

		<label class="my-4 block">
			Type
			<select name="type">
				<option @selected(old('type', $user->type) == 'admin')>admin</option>
				<option @selected(old('type', $user->type) == 'basic')>basic</option>
			</select>
		</label>

		<button type="submit">Save</button>

		@if ($user->id != null)
			<button name="delete" type="submit" value="1" onclick="return confirm('Are you sure?')">Delete</button>
		@endif
	</form>
@endsection
