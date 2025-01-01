@extends('layouts.default')
@section('title', 'Transactions')

@section('content')
	{{-- <a href="/t/new">New transaction</a> --}}
	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Amount</th>
				<th>Fund / Budget</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			@if ($transactions->isEmpty())
				<tr>
					<td colspan="4">No transactions found</td>
				</tr>
			@endif
			@foreach ($transactions as $transaction)
				<tr>
					<td>{{ $transaction->date->format('Y-m-d') }}</td>
					<td>{{ $transaction->amount }}</td>
					<td>{{ $transaction->fund->name ?? $transaction->budget->name }}</td>
					<td>{{ $transaction->description }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
