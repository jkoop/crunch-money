@extends('layouts.default')
@section('title', 'Budgets')

@section('content')
	<table>
		<thead>
			<tr>
				<th class="w-full md:w-fit md:min-w-96">Name</th>
				<th>Balance</th>
			</tr>
		</thead>
		<tbody>
			@if ($budgets->isEmpty())
				<tr>
					<td colspan="2">No budgets found; if you're not a new user, this should never happen</td>
				</tr>
			@endif
			@foreach ($budgets as $budget)
				<tr>
					<td><a href="/b/{{ $budget->slug }}">{{ $budget->name }}</a></td>
					<td class="{{ $budget->balance < 0 ? 'bg-red-700 text-white' : '' }} number">@money($budget->balance)</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
