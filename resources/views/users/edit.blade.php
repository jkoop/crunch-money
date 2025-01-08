@extends('layouts.admin')
@section('title', $user->name . ' - Users')

@section('content')
	<form method="post">
		@csrf
		<table>
			<tbody>
				<tr>
					<th>Name</th>
					<td><input name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" /></td>
				</tr>
				<tr>
					<th>Token</th>
					<td>
						<label><input name="regenerate_token" type="checkbox" @checked($user->id == null) @disabled($user->id == null) />
							Regenerate </label>
						Will log out the user of all devices.
					</td>
				</tr>
				<tr>
					<th>Notes</th>
					<td>
						<textarea name="notes" maxlength="65535">{{ $user->notes }}</textarea>
						Not shown to the user; for your convenience.
					</td>
				</tr>
				<tr>
					<th>Type</th>
					<td><select name="type">
							<option @selected(old('type', $user->type) == 'admin')>admin</option>
							<option @selected(old('type', $user->type) == 'basic')>basic</option>
						</select></td>
				</tr>
			</tbody>
		</table>

		<button type="submit">Save</button>

		@if ($user->id != null)
			<button name="delete" type="submit" value="1">Delete</button>
		@endif
	</form>
@endsection
