@extends('layouts.default')
@section('title', $title)

@section('content')
	<form action="{{ route('funds.post', ['slug' => $fund?->slug ?? 'new']) }}" method="post">
		@csrf
		<label for="name">Name</label>
		<input name="name" type="text" value="{{ old('name', $fund?->name) }}" required maxlength="255" />
		<button type="submit">Save</button>
	</form>

	<h2>Transactions</h2>

	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Amount</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($fund->transactions()->orderByDesc('date')->get() as $transaction)
				<tr>
					<td>{{ $transaction->date->format('Y-m-d') }}</td>
					<td>{{ $transaction->amount }}</td>
					<td>{{ $transaction->getDescription() }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
