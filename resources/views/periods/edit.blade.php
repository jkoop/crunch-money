@extends('layouts.default')
@section('title', $period->start->format('D M j Y') . ' - Periods')

@section('content')
	<form action="/p/{{ $period->start->format('Y-m-d') }}" method="post" x-data="data">
		@csrf
		<label for="start">Start date</label>
		<input name="start" type="date" value="{{ $period->start->format('Y-m-d') }}">
		<br>
		<label for="end">End date</label>
		<input name="end" type="date" value="{{ $period->end->format('Y-m-d') }}">
		<br>

		<table>
			<thead>
				<tr>
					<th>Income <button type="button" x-on:click="incomes.push({ id: null, name: '', amount: null })">+</button></th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>
				<template x-for="income in incomes">
					<tr>
						<td>
							<input name="incomes[id]" type="hidden" x-model="income.id">
							<input name="incomes[name]" type="text" maxlength="255" x-model="income.name" required />
						</td>
						<td><input name="incomes[amount]" type="number" step="0.01" min="0" x-model="income.amount" required />
						</td>
						<td>
							<button type="button" x-cloak x-show="incomes.length > 1"
								x-on:click="incomes = incomes.filter(i => i.id != income.id)">x</button>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<table>
			<thead>
				<tr>
					<th>Budget <button type="button" x-on:click="budgets.push({ id: null, name: '', amount: null })">+</button></th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>
				<template x-for="budget in budgets">
					<tr>
						<td>
							<input name="budgets[id]" type="hidden" x-model="budget.id">
							<input name="budgets[name]" type="text" maxlength="255" x-model="budget.name" required />
						</td>
						<td><input name="budgets[amount]" type="number" step="0.01" min="0" x-model="budget.amount" required />
						</td>
						<td>
							<button type="button" x-cloak x-show="budgets.length > 1"
								x-on:click="budgets = budgets.filter(b => b.id != budget.id)">x</button>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<table>
			<thead>
				<tr>
					<th>Fund <button type="button" x-on:click="funds.push({ id: null, name: '', balance: 0, amount: null })">+</button>
					</th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>
				<template x-for="fund in funds">
					<tr>
						<td>
							<input name="funds[id]" type="hidden" x-model="fund.id">
							<input name="funds[name]" type="text" maxlength="255" x-model="fund.name" required />
						</td>
						<td><input name="funds[amount]" type="number" step="0.01" x-model="fund.amount" required /></td>
						<td>
							<button type="button" x-cloak x-show="funds.length > 1"
								x-on:click="funds = funds.filter(f => f.id != fund.id)">x</button>
							Balance: <span x-text="fund.balance"></span>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<button type="submit"
			x-bind:disabled="incomes.length == 0 || budgets.length == 0 || funds.length == 0 || surplus() != 0">Save</button>
		<span x-bind:class="{ 'bg-green-500': surplus() > 0, 'bg-red-500': surplus() < 0 }">Surplus: $<span
				x-text="Math.round(surplus() * 100) / 100"></span></span>
	</form>

	<script>
		window.addEventListener('alpine:init', () => {
			Alpine.data('data', () => {
				return {
					incomes: {{ Js::from($period->incomes) }},
					budgets: {{ Js::from($period->budgets) }},
					funds: {{ Js::from($funds) }},
					surplus: function() {
						return this.incomes.reduce((acc, income) => acc + income.amount, 0) -
							this.budgets.reduce((acc, budget) => acc + budget.amount, 0) -
							this.funds.reduce((acc, fund) => acc + fund.amount, 0);
					},
				}
			})
		});
	</script>
@endsection
