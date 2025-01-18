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

		<label class="mb-4 block">
			Date format
			<select name="date_format">
				<option value="mdy" @selected(old('date_format', $user->date_format) == 'mdy')>Sat Jan 1 2000</option>
				<option value="dmy" @selected(old('date_format', $user->date_format) == 'dmy')>Sat 1 Jan 2000</option>
				<option value="ymd" @selected(old('date_format', $user->date_format) == 'ymd')>Sat 2000 Jan 1</option>
			</select>
		</label>

		<label class="block">
			<input name="two_digit_year" type="checkbox" @checked(old('two_digit_year', $user->two_digit_year) != null) />
			Two digit year
		</label>

		<label class="block">
			<input name="show_dow_on_tables" type="checkbox" @checked(old('show_dow_on_tables', $user->show_dow_on_tables) != null) />
			Show day of week on tables
		</label>

		<label class="block">
			<input name="show_dow_on_period_picker" type="checkbox" @checked(old('show_dow_on_period_picker', $user->show_dow_on_period_picker) != null) />
			Show day of week on overhead period picker
		</label>

		<label class="mb-4 block">
			<input name="always_show_year" type="checkbox" @checked(old('always_show_year', $user->always_show_year) != null) />
			Always show year
		</label>

		@can('edit-profile')
			<button type="submit">Save</button>
			<button type="button" disabled>Delete</button>
			<span class="text-sm text-gray-400">Contact your system admin to delete your account.</span>
		@endcan
	</form>
@endsection
