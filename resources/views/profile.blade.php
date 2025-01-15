@extends('layouts.default')
@section('title', 'Profile')

@section('content')
	<form method="post">
		@csrf

		<label>
			Name
			<input name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255"
				@disabled(!Gate::allows('edit-profile')) />
		</label>
		<p class="mb-4 text-sm text-gray-400">Not shown to anyone; for your convenience.</p>

		<label>
			<input name="regenerate_token" type="checkbox" @disabled(!Gate::allows('edit-profile')) /> Regenerate Token
		</label>
		<p class="mb-4 text-sm text-gray-400">This will log you out of all devices.</p>

		@can('edit-profile')
			<button type="submit">Save</button>
			<button type="button" disabled>Delete</button>
			<span class="text-sm text-gray-400">Contact your system admin to delete your account.</span>
		@endcan
	</form>
@endsection
