@extends('layouts.admin')
@section('title', 'Users')

@section('content')
	<a href="/u/new">New user</a>
	<table class="my-4">
		<thead>
			<tr>
				<th>User</th>
				<th>Budgets</th>
				<th>Funds</th>
				<th>Transactions</th>
				<th>Periods</th>
				<th>Type</th>
				<th>Notes</th>
			</tr>
		</thead>
		<tbody>
			@foreach (App\Models\User::orderBy('name')->get() as $user)
				<tr>
					<td><a href="{{ route('users.get', $user) }}">{{ $user->name }}</a></td>
					<td class="number">{{ $user->budgets()->count() }}</td>
					<td class="number">{{ $user->funds()->count() }}</td>
					<td class="number">{{ $user->transactions()->count() }}</td>
					<td class="number">{{ $user->periods()->count() }}</td>
					<td>{{ $user->type }}</td>
					<td>{{ explode("\n", $user->notes)[0] }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
