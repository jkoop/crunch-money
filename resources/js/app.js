import "./bootstrap";
import "./period";
import Alpine from "alpinejs";

window.Alpine = Alpine;

window.strToFloat = function (string) {
	string = ("" + string).replaceAll(/[^0-9\.-]/g, "");
	const float = parseFloat(string);
	if (isNaN(float)) return 0;
	return float;
};

window.money = (amount) => {
	return new Intl.NumberFormat("en-US", {
		style: "decimal",
		minimumFractionDigits: 2,
		maximumFractionDigits: 2,
	}).format(strToFloat(amount));
};

window.percent = (amount) => {
	amount = amount.replace("%", "");
	amount = strToFloat(amount);
	return amount + "%";
};

window.getToday = () => {
	const date = new Date();
	date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
	return date.toISOString().split("T")[0];
};

window.addEventListener("DOMContentLoaded", () => {
	const links = document.querySelectorAll('nav a:not([href^="/p/"])');
	links.forEach((link) => {
		if (window.location.pathname == link.pathname || window.location.pathname.startsWith(link.pathname + "/")) {
			link.classList.add("current");
		}
	});
});

function movePeriodPicker() {
	const periodPickerContainer = document.getElementById("period-picker-container");
	const spacers = document.querySelectorAll("nav > div:last-of-type > span.flex-grow");
	if (periodPickerContainer == null) return; // admin layout doesn't have this

	// md breakpoint
	if (window.innerWidth >= 768) {
		spacers[0].after(periodPickerContainer);
	} else {
		spacers[0].closest("div").before(periodPickerContainer);
	}
}

window.addEventListener("resize", movePeriodPicker);
movePeriodPicker();

if (window.downloads != undefined) {
	window.downloads.forEach((download) => {
		// download.content is the content of the file, not a URL
		const url = URL.createObjectURL(new Blob([download.content]));
		const link = document.createElement("a");
		link.href = url;
		link.download = download.name;
		link.click();

		// notify the server that the download was successful
		axios.delete(`/downloads/${download.id}`);
	});
}

window.dispatchEvent(new Event("js-ready"));
Alpine.start();
