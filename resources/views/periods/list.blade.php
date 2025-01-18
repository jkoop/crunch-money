@extends('layouts.default')
@section('title', 'Periods')

@section('content')
	<a href="/p/new">New period</a>

	<table class="my-4">
		<thead>
			<tr>
				<th>Start date</th>
				<th>End date</th>
			</tr>
		</thead>
		<tbody>
			@if ($periods->isEmpty())
				<tr>
					<td colspan="2">No periods found; this should never happen</td>
				</tr>
			@endif
			@foreach ($periods as $period)
				<tr>
					<td><a href="/p/{{ (string) $period->start }}">{{ $period->start->format() }}</a></td>
					<td>{{ $period->end->format() }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
