@extends('layouts.default')
@section('title', 'Profile')

@section('content')
	<form method="post">
		@csrf
		<table>
			<tbody>
				<tr>
					<th>Name</th>
					<td><input name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" /> Not shown to anyone;
						for your convenience.</td>
				</tr>
				<tr>
					<th>Token</th>
					<td>
						<label><input name="regenerate_token" type="checkbox" /> Regenerate </label>
						Will log you out of all devices.
					</td>
				</tr>
			</tbody>
		</table>
		<button type="submit">Save</button>
		<button type="button" disabled>Delete</button>
		<span>Contact your system admin to delete your account.</span>
	</form>
@endsection
