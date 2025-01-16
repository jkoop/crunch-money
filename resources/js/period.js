window.addEventListener("alpine:init", () => {
	if (window.period == undefined) return;

	Alpine.data("period", () => {
		const incomes = window.period.incomes;
		const budgets = window.period.budgets;
		const funds = window.period.funds;

		incomes.unshift({
			id: "carryover",
			name: "carryover from last period",
			amount: window.period.carryover,
		});

		return {
			asynchronousDataFlying: false,
			asynchronousDataFlyAgain: false,
			start: window.period.start,
			end: window.period.end,
			incomes,
			budgets,
			funds,

			init: function () {
				for (const budget of budgets) {
					budget.amountDollar = "";
				}
				for (const fund of funds) {
					fund.amountDollar = "";
				}
				this.updateAsynchronousData();
				this.updateAmountDollars();
			},

			amountToAmountDollar: function (amount) {
				if (amount == undefined) amount = "";
				amount = "" + amount;

				const wasPercentage = amount.endsWith("%");
				amount = strToFloat(amount);

				if (wasPercentage) {
					const totalIncome = this.incomes
						.filter((income) => income.id != "carryover")
						.reduce((acc, income) => acc + this.amountToAmountDollar(income.amount).dollar, 0);
					amount = Math.round((amount / 100) * totalIncome * 100) / 100;
				}

				return {
					wasPercentage,
					dollar: amount,
				};
			},

			updateAmountDollar: function (account, value) {
				const { wasPercentage, dollar } = this.amountToAmountDollar(value);

				if (wasPercentage) {
					account.amountDollar = dollar;
				} else {
					account.amountDollar = "";
				}
			},

			updateAmountDollars: function () {
				for (const budget of this.budgets) {
					this.updateAmountDollar(budget, budget.amount);
				}
				for (const fund of this.funds) {
					this.updateAmountDollar(fund, fund.amount);
				}
			},

			updateAsynchronousData: async function () {
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

			surplus: function () {
				return (
					Math.round(
						(0 +
							this.incomes.reduce((acc, income) => acc + strToFloat(money(income.amount)), 0) -
							this.budgets.reduce(
								(acc, budget) => acc + this.amountToAmountDollar(budget.amount).dollar,
								0
							) -
							this.funds.reduce((acc, fund) => acc + this.amountToAmountDollar(fund.amount).dollar, 0)) *
							100
					) / 100
				);
			},

			saveButtonText: function () {
				if (new Date(this.start) > new Date(this.end)) return "Start date must not be after end date";
				if (this.incomes.length < 2) return "You must have at least one income";
				if (this.budgets.length < 1) return "You must have at least one budget";
				if (this.funds.length < 1) return "You must have at least one fund";
				if (this.surplus() != 0) return "You must have a surplus of 0";
				return null;
			},
		};
	});
});
