@extends('layouts.default')
@section('title', $period->start->format('D M j Y') . ' - Periods')

@section('content')
	<form action="/p/{{ $slug }}" method="post" x-data="data">
		@csrf
		<label for="start">Start date</label>
		<input id="start" name="start" type="date" x-model="start">
		<br>
		<label for="end">End date</label>
		<input name="end" type="date" x-model="end">
		<br>

		<table>
			<thead>
				<tr>
					<th>Income <button type="button" x-on:click="incomes.push({ id: null, name: '', amount: null })">+</button></th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>
				<template x-for="(income, index) in incomes">
					<tr>
						<td>
							<input type="hidden" x-bind:name="`incomes[${index}][id]`" x-model="income.id">
							<input type="text" x-bind:name="`incomes[${index}][name]`" maxlength="255" x-model="income.name" required />
						</td>
						<td><input type="number" x-bind:name="`incomes[${index}][amount]`" step="0.01" min="0"
								x-model="income.amount" required />
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
				<template x-for="(budget, index) in budgets">
					<tr>
						<td>
							<input type="hidden" x-bind:name="`budgets[${index}][id]`" x-model="budget.id">
							<input type="text" x-bind:name="`budgets[${index}][name]`" maxlength="255" x-model="budget.name" required />
						</td>
						<td><input type="number" x-bind:name="`budgets[${index}][amount]`" step="0.01" min="0"
								x-model="budget.amount" required />
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
					<th>
						Fund
						<button type="button" x-on:click="funds.push({ id: null, name: '', amount: null }); updateFundBalances()">
							+
						</button>
					</th>
					<th>Amount</th>
				</tr>
			</thead>
			<tbody>
				<template x-for="(fund, index) in funds">
					<tr>
						<td>
							<input type="hidden" x-bind:name="`funds[${index}][id]`" x-model="fund.id">
							<input type="text" x-bind:name="`funds[${index}][name]`" maxlength="255" x-model="fund.name" required />
						</td>
						<td><input type="number" x-bind:name="`funds[${index}][amount]`" step="0.01" x-model="fund.amount" required />
						</td>
						<td>
							<button type="button" x-cloak x-show="funds.length > 1 && fund.balance == 0"
								x-on:click="funds = funds.filter(f => f.id != fund.id)">x</button>
							Balance: <span x-text="fund.balance"></span>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<button type="submit" x-text="saveButtonText()"
			x-bind:disabled="incomes.length == 0 || budgets.length == 0 || funds.length == 0 || surplus() != 0"></button>
		<span x-bind:class="{ 'bg-green-500': surplus() > 0, 'bg-red-500': surplus() < 0 }">Surplus: <span
				x-text="Math.round(surplus() * 100) / 100"></span></span>
	</form>

	<script>
		window.addEventListener('alpine:init', () => {
			Alpine.data('data', () => {
				return {
					init: function() {
						this.updateFundBalances();
						document.getElementById("start")
							.addEventListener("change", () => {
								this.updateFundBalances();
							});
					},
					startFlying: false,
					startFlyAgain: false,
					start: "{{ $period->start->format('Y-m-d') }}",
					end: "{{ $period->end->format('Y-m-d') }}",
					incomes: {{ Js::from($period->incomes) }},
					budgets: {{ Js::from($budgets) }},
					funds: {{ Js::from($funds) }},
					updateFundBalances: async function() {
						if (this.startFlying) {
							this.startFlyAgain = true;
							return;
						}
						this.startFlying = true;

						for (const fund of this.funds) {
							fund.balance = undefined;
						}

						try {
							const response = await fetch(`/f/_balances?date=${this.start}`);
							const balances = await response.json();
							for (const fund of this.funds) {
								fund.balance = balances[fund.id] ?? 0;
							}
						} catch (err) {
							alert("Failed to fetch fund balances");
							this.startFlying = false;
						}

						this.startFlying = false;
						if (this.startFlyAgain) {
							this.startFlyAgain = false;
							this.updateFundBalances();
						}
					},
					surplus: function() {
						return 0 +
							this.incomes.reduce((acc, income) => acc + parseInt("0" + income.amount), 0) -
							this.budgets.reduce((acc, budget) => acc + parseInt("0" + budget.amount), 0) -
							this.funds.reduce((acc, fund) => acc + parseInt("0" + fund.amount), 0);
					},
					saveButtonText: function() {
						if (this.incomes.length < 1) return "You must have at least one income";
						if (this.budgets.length < 1) return "You must have at least one budget";
						if (this.funds.length < 1) return "You must have at least one fund";
						if (this.surplus() != 0) return "You must have a surplus of 0";
						return "Save";
					}
				}
			})
		});
	</script>
@endsection
