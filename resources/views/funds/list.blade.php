@extends('layouts.default')
@section('title', 'Funds')

@section('content')
	<a href="/f/new">New fund</a>
	<table>
		<thead>
			<tr>
				<th>Name</th>
				<th>Balance</th>
			</tr>
		</thead>
		<tbody>
			@if ($funds->isEmpty())
				<tr>
					<td colspan="2">No funds found</td>
				</tr>
			@endif
			@foreach ($funds as $fund)
				<tr>
					<td><a href="/f/{{ $fund->slug }}">{{ $fund->name }}</a></td>
					<td>{{ $fund->balance }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
