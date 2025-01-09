@extends('layouts.default')
@section('title', 'Funds')

@section('content')
	<form class="mb-4">
		<label>
			<input name="all" type="checkbox" onchange="this.form.submit()" @checked($showAll) /> Show all
		</label>
	</form>

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
					<td class="number">{{ $fund->balance }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
