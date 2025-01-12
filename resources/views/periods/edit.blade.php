@extends('layouts.default')
@section('title', $period->start->format('D M j Y') . ' - Periods')

@section('content')
	<script>
		window.period = {
			id: {{ Js::from($period->id) }},
			incomes: {{ Js::from($period->incomes->sortBy('name')->values()) }},
			carryover: {{ Js::from($period->carryover) }},
			budgets: {{ Js::from($budgets->sortBy('name')->values()) }},
			funds: {{ Js::from($funds->sortBy('name')->values()) }},
			start: {{ Js::from($period->start->format('Y-m-d')) }},
			end: {{ Js::from($period->end->format('Y-m-d')) }},
		};
	</script>

	<form action="/p/{{ $slug }}" method="post" x-data="period">
		@csrf

		<div class="grid w-fit items-center gap-2 gap-y-0" style="grid-template-columns: auto auto">
			<label for="start">Start date</label>
			<input name="start" type="date" x-on:change="updateAsynchronousData()" x-model="start">
			<label for="end">End date</label>
			<input name="end" type="date" x-model="end">
		</div>

		<h2 class="my-4">
			Incomes
			<button class="relative -top-0.5 -my-1 py-0 text-base font-normal" type="button"
				x-on:click="incomes.push({ id: 'new' + Math.random().toString(36).substring(2, 15), name: '', amount: null })">+</button>
		</h2>

		<table class="my-4">
			<thead>
				<tr>
					<th>Name</th>
					<th>Amount</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<template x-for="(income, index) in incomes">
					<tr>
						<td>
							<input type="hidden" x-bind:name="income.id == 'carryover' ? '' : `incomes[${index}][id]`" x-model="income.id">
							<input type="text" x-bind:name="income.id == 'carryover' ? '' : `incomes[${index}][name]`" maxlength="255"
								x-model="income.name" x-bind:disabled="income.id == 'carryover'" required />
						</td>
						<td><input class="number w-24" type="text"
								title="A numerical value consisting of a whole number followed by an optional decimal point and up to two digits."
								pattern="^\d+(\.\d{1,2})?$" x-bind:name="income.id == 'carryover' ? '' : `incomes[${index}][amount]`"
								step="0.01" min="0" x-bind:disabled="income.id == 'carryover'" x-model="income.amount"
								x-on:input="updateAmountDollars()" required />
						</td>
						<td>
							<button class="p-0 px-2 text-base" type="button" x-cloak
								x-bind:disabled="incomes.length <= 1 || income.id == 'carryover'"
								x-on:click="incomes = incomes.filter(i => i.id != income.id); updateAmountDollars()">&times;</button>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<h2 class="my-4">
			Budgets
			<button class="relative -top-0.5 -my-1 py-0 text-base font-normal" type="button"
				x-on:click="budgets.push({ id: 'new' + Math.random().toString(36).substring(2, 15), name: '', amount: null })">+</button>
		</h2>

		<table class="my-4">
			<thead>
				<tr>
					<th>Name</th>
					<th>Amount</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<template x-for="(budget, index) in budgets">
					<tr>
						<td>
							<input type="hidden" x-bind:name="`budgets[${index}][id]`" x-model="budget.id">
							<input type="text" x-bind:name="`budgets[${index}][name]`" maxlength="255" x-model="budget.name" required />
						</td>
						<td class="relative">
							<input class="number w-24" type="text"
								title="A numerical value consisting of a whole number followed by an optional decimal point and up to two digits. A percent sign (%) may follow."
								pattern="^\d+(\.\d{1,2})?%?$" x-bind:name="`budgets[${index}][amount]`" x-model="budget.amount"
								x-bind:class="{ '!text-left': ('' + budget.amountDollar).length > 0 }" x-on:input="updateAmountDollar(budget)"
								required />
							<span class="number absolute bottom-0 right-4 top-0 my-auto h-min text-xs" x-text="budget.amountDollar"></span>
						</td>
						<td>
							<button class="p-0 px-2 text-base" type="button" x-cloak x-bind:disabled="budgets.length <= 1"
								x-on:click="budgets = budgets.filter(b => b.id != budget.id)">&times;</button>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<h2 class="my-4">
			Funds
			<button class="relative -top-0.5 -my-1 py-0 text-base font-normal" type="button"
				x-on:click="funds.push({ id: 'new' + Math.random().toString(36).substring(2, 15), name: '', amount: null }); updateAsynchronousData()">+</button>
		</h2>

		<table class="my-4">
			<thead>
				<tr>
					<th>Name</th>
					<th>Amount</th>
					<th>Opening Balance</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<template x-for="(fund, index) in funds">
					<tr>
						<td>
							<input type="hidden" x-bind:name="`funds[${index}][id]`" x-model="fund.id">
							<input type="text" x-bind:name="`funds[${index}][name]`" maxlength="255" x-model="fund.name" required />
						</td>
						<td class="relative">
							<input class="number w-24" type="text"
								title="A numerical value that may be preceded by a minus sign (indicating a negative number), consisting of a whole number followed by an optional decimal point and up to two digits. A percent sign (%) may follow."
								x-bind:class="{ '!text-left': ('' + fund.amountDollar).length > 0 }" pattern="^-?\d+(\.\d{1,2})?%?$"
								x-bind:name="`funds[${index}][amount]`" x-model="fund.amount" x-on:input="updateAmountDollar(fund)" required />
							<span class="number absolute bottom-0 right-4 top-0 my-auto h-min text-xs" x-text="fund.amountDollar"></span>
						</td>
						<td class="number" x-text="fund.balance"></td>
						<td>
							<button class="p-0 px-2 text-base" type="button" x-cloak
								x-bind:disabled="funds.length <= 1 || fund.balance != 0"
								x-on:click="funds = funds.filter(f => f.id != fund.id)">&times;</button>
						</td>
					</tr>
				</template>
			</tbody>
		</table>

		<div class="flex flex-row gap-2 align-middle">
			<button type="submit" x-text="saveButtonText() ?? 'Save'" x-bind:disabled="saveButtonText() != null"></button>
			<span
				x-bind:class="{ 'bg-green-700 px-2': surplus() > 0, 'text-gray-400': surplus() == 0, 'bg-red-700 px-2': surplus() < 0 }">Surplus:
				<span x-text="Math.round(surplus() * 100) / 100"></span></span>
		</div>
	</form>

	{{-- I couldn't get this working if I moved it to app.js. Functions weren't available. --}}
	<script>
		window.addEventListener("alpine:init", () => {
			if (window.period == undefined) return;

			Alpine.data("period", () => {
				const incomes = window.period.incomes;
				incomes.unshift({
					id: "carryover",
					name: "carryover from last period",
					amount: window.period.carryover,
				});

				const budgets = window.period.budgets;
				const funds = window.period.funds;

				return {
					init: function() {
						for (const budget of budgets) {
							budget.amountDollar = "";
						}
						for (const fund of funds) {
							fund.amountDollar = "";
						}
						this.updateAsynchronousData();
						this.updateAmountDollars();
					},
					amountToAmountDollar: function(amount) {
						if (amount == undefined) amount = "";
						amount = "" + amount;

						const wasPercentage = amount.endsWith("%");
						amount = parseFloat((amount[0] == "-" ? "-" : "") + "0" + amount.replace("-", ""));

						if (wasPercentage) {
							const totalIncome = this.incomes
								.filter((income) => income.id != "carryover")
								.reduce((acc, income) => acc + this.amountToAmountDollar(income.amount)
									.dollar, 0);
							amount = Math.round((amount / 100) * totalIncome * 100) / 100;
						}

						return {
							wasPercentage,
							dollar: amount,
						};
					},
					updateAmountDollar: function(account) {
						const {
							wasPercentage,
							dollar
						} = this.amountToAmountDollar(account.amount);

						if (wasPercentage) {
							account.amountDollar = dollar;
						} else {
							account.amountDollar = "";
						}
					},
					updateAmountDollars: function() {
						for (const budget of this.budgets) {
							this.updateAmountDollar(budget);
						}
						for (const fund of this.funds) {
							this.updateAmountDollar(fund);
						}
					},
					asynchronousDataFlying: false,
					asynchronousDataFlyAgain: false,
					start: window.period.start,
					end: window.period.end,
					incomes,
					budgets,
					funds,
					updateAsynchronousData: async function() {
						if (this.asynchronousDataFlying) {
							this.asynchronousDataFlyAgain = true;
							return;
						}
						this.asynchronousDataFlying = true;

						for (const fund of this.funds) {
							fund.balance = undefined;
						}
						this.incomes.forEach((income) => {
							if (income.id != "carryover") return;
							income.amount = undefined;
						});

						const promises = [];
						promises.push(
							axios(`/f/_balances?date=${this.start}`)
							.then((response) => {
								for (const fund of this.funds) {
									fund.balance = response.data[fund.id] ?? 0;
								}
							})
							.catch((err) => {
								alert("Failed to fetch fund balances");
								console.error(err);
							})
						);

						promises.push(
							axios(`/p/_carryover?new_start=${this.start}&exclude=${window.period.id}`)
							.then((response) => {
								this.incomes.forEach((income) => {
									if (income.id != "carryover") return;
									income.amount = response.data;
								});
							})
							.catch((err) => {
								alert("Failed to fetch carryover");
								console.error(err);
							})
						);

						await Promise.all(promises);

						this.asynchronousDataFlying = false;
						if (this.asynchronousDataFlyAgain) {
							this.asynchronousDataFlyAgain = false;
							this.updateAsynchronousData();
						}
					},
					surplus: function() {
						return (
							Math.round(
								(0 +
									this.incomes.reduce(
										(acc, income) => acc + this.amountToAmountDollar(income.amount)
										.dollar,
										0
									) -
									this.budgets.reduce(
										(acc, budget) => acc + this.amountToAmountDollar(budget.amount)
										.dollar,
										0
									) -
									this.funds.reduce((acc, fund) => acc + this.amountToAmountDollar(
										fund.amount).dollar, 0)) *
								100
							) / 100
						);
					},
					saveButtonText: function() {
						if (new Date(this.start) > new Date(this.end))
							return "Start date must not be after end date";
						if (this.incomes.length < 1) return "You must have at least one income";
						if (this.budgets.length < 1) return "You must have at least one budget";
						if (this.funds.length < 1) return "You must have at least one fund";
						if (this.surplus() != 0) return "You must have a surplus of 0";
						return null;
					},
				};
			});
		});
	</script>
@endsection
