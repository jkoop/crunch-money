@extends('layouts.default')
@section('title', $budget->name . ' - Budgets')

@section('content')
	<form action="{{ route('budgets.post', $budget->slug) }}" method="post">
		@csrf
		<label for="name">Name</label>
		<input name="name" type="text" value="{{ old('name', $budget->name) }}" required maxlength="255" />
		<button type="submit">Save</button>
	</form>

	<p class="{{ $budget->balance < 0 ? 'text-red-500' : '' }} text-xl font-bold">Balance: {{ $budget->balance }}</p>

	<h2>Transactions</h2>

	<form action="{{ route('transactions.post') }}" method="post">
		@csrf
		<input name="negate" type="hidden" value="1" />
		<input name="budget_id" type="hidden" value="{{ $budget->id }}" />
		<input name="date" type="date" value="{{ now()->format('Y-m-d') }}" required />
		<input name="amount" type="number" style="width: 100px;" required placeholder="withdraw amount" autofocus />
		<input name="description" type="text" required placeholder="description" maxlength="255" />
		<button type="submit">Add Transaction</button>
	</form>

	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Amount</th>
				<th>Description</th>
			</tr>
		</thead>
		@foreach ($budget->transactions()->orderBy('date', 'desc')->orderBy('id', 'desc')->get() as $transaction)
			<tr>
				<td>{{ $transaction->date->format('D M j Y') }}</td>
				<td class="text-right">{{ $transaction->amount }}</td>
				<td>{{ $transaction->description }}</td>
			</tr>
		@endforeach
	</table>
@endsection
