@extends('layouts.default')
@section('title', $title)

@section('content')
	<form action="{{ route('funds.post', ['slug' => $fund?->slug ?? 'new']) }}" method="post">
		@csrf
		<fieldset>
			<legend>Fund</legend>
			<label for="name">Name</label>
			<input name="name" type="text" value="{{ old('name', $fund?->name) }}" required maxlength="255" />
			<button type="submit">Save</button>
		</fieldset>
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
			@foreach ($fund->transactions()->orderByDesc('id', 'date')->get() as $transaction)
				<tr>
					<td>{{ $transaction->date->format() }}</td>
					<td class="number">@money($transaction->amount)</td>
					<td>{{ $transaction->getDescription() }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
