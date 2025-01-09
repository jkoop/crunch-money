@extends('layouts.default')
@section('title', $budget->name . ' - Budgets')

@section('content')
	<form action="{{ route('budgets.post', $budget->slug) }}" method="post">
		@csrf
		<fieldset>
			<legend>Budget</legend>
			<label for="name">Name</label>
			<input name="name" type="text" value="{{ old('name', $budget->name) }}" required maxlength="255" />
			<button type="submit">Save</button>
		</fieldset>
	</form>

	<p
		class="{{ $budget->balance < 0 ? 'bg-red-700 ' : '' }} {{ $budget->balance > 0 ? 'bg-green-700' : '' }} my-4 max-w-fit px-2 text-xl font-bold">
		Balance: <span class="number">{{ $budget->balance }}</span>
	</p>

	<h2>Transactions</h2>

	<form class="my-4" action="{{ route('transactions.post') }}" method="post">
		@csrf
		<input name="negate" type="hidden" value="1" />
		<input name="budget_id" type="hidden" value="{{ $budget->id }}" />
		<input name="date" type="date" required />
		<script>
			window.addEventListener("js-ready", () => {
				document.querySelector('input[name="date"]').value = getToday();
			});
		</script>
		<input class="number" name="amount" type="number" style="width: 100px;" step="0.01" required autofocus
			placeholder="withdraw amount" />
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
				<td class="number">{{ $transaction->amount }}</td>
				<td>{{ $transaction->getDescription() }}</td>
			</tr>
		@endforeach
	</table>
@endsection
