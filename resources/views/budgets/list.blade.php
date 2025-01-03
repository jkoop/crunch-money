@extends('layouts.default')
@section('title', 'Budgets')

@section('content')
	<a href="/p/{{ Period::current()->start->format('Y-m-d') }}">Edit period</a>
	<table>
		<thead>
			<tr>
				<th>Name</th>
				<th>Balance</th>
			</tr>
		</thead>
		<tbody>
			@if ($budgets->isEmpty())
				<tr>
					<td colspan="2">No budgets found; this should never happen</td>
				</tr>
			@endif
			@foreach ($budgets as $budget)
				<tr>
					<td><a href="/b/{{ $budget->slug }}">{{ $budget->name }}</a></td>
					<td class="{{ $budget->balance < 0 ? 'bg-red-500 text-white' : '' }} text-right">{{ $budget->balance }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
