@extends('layouts.default')
@section('title', $period->start->format('D M j Y') . ' - Periods')

@section('content')
	<script>
		window.period = {
			id: {{ Js::from($period->id) }},
			start: {{ Js::from($period->start->format('Y-m-d')) }},
			end: {{ Js::from($period->end->format('Y-m-d')) }},
			incomes: {{ Js::from($period->incomes->sortBy('name')->values()) }},
			carryover: {{ Js::from($period->carryover) }},
			budgets: {{ Js::from($budgets->sortBy('name')->values()) }},
			funds: {{ Js::from($funds->sortBy('name')->values()) }},
		};
	</script>

	<form action="/p/{{ $slug }}" method="post" x-data="period"
		x-on:submit="(saveButtonText() != null) ? $el.submit() : false">
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
								step="0.01" min="0" x-bind:disabled="income.id == 'carryover'" x-bind:value="money(income.amount)"
								x-on:blur="income.amount = $el.value" x-on:input="updateAmountDollars()" required />
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
								pattern="^\d+(\.\d{1,2})?%?$" x-bind:name="`budgets[${index}][amount]`"
								x-bind:value="('' + budget.amount).includes('%') ? percent(budget.amount) : money(budget.amount)"
								x-on:blur="budget.amount = $el.value" x-bind:class="{ '!text-left': ('' + budget.amountDollar).length > 0 }"
								x-on:input="updateAmountDollar(budget, $el.value)" required />
							<span class="number absolute bottom-0 right-4 top-0 my-auto h-min text-xs"
								x-show="('' + budget.amountDollar).length > 0" x-text="money(budget.amountDollar)"></span>
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
								x-bind:name="`funds[${index}][amount]`"
								x-bind:value="('' + fund.amount).includes('%') ? percent(fund.amount) : money(fund.amount)"
								x-on:blur="fund.amount = $el.value" x-on:input="updateAmountDollar(fund, $el.value)" required />
							<span class="number absolute bottom-0 right-4 top-0 my-auto h-min text-xs"
								x-show="('' + fund.amountDollar).length > 0" x-text="money(fund.amountDollar)"></span>
						</td>
						<td class="number" x-text="money(fund.balance)"></td>
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
@endsection
