@extends('layouts.default')
@section('title', 'Transactions')

@section('content')
	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Amount</th>
				<th>Budget / Fund</th>
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
					<td class="number">@money($transaction->amount)</td>
					<td>
						@if ($transaction->fund != null)
							<a href="/f/{{ $transaction->fund->slug }}">{{ $transaction->fund->name }}</a>
						@elseif ($transaction->budget != null)
							<a href="/b/{{ $transaction->budget->slug }}">{{ $transaction->budget->name }}</a>
						@else
							none; @impossible
						@endif
					</td>
					<td>{{ $transaction->getDescription() }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
