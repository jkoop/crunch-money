import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

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

window.dispatchEvent(new Event("js-ready"));
