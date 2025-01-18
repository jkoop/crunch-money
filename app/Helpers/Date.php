<?php

namespace App\Helpers;

use App\Exceptions\ImpossibleStateException;
use Illuminate\Support\Facades\Auth;

class Date implements \Stringable {
	/**
	 * @param int $year
	 * @param int $month Jan = 1, Dec = 12
	 * @param int $day
	 */
	public function __construct(readonly int $year, readonly int $month, readonly int $day) {
		if ($year < 1 || $year > 9999) {
			throw new \InvalidArgumentException('$year is not in range 1 to 9999.');
		}

		if ($month < 1 || $month > 12) {
			throw new \InvalidArgumentException('$month is not in range 1 to 12.');
		}

		$lastDayOfMonth = self::getLastDayOfMonth($year, $month);
		if ($day < 1 || $day > $lastDayOfMonth) {
			throw new \InvalidArgumentException('$day is not is range 1 to ' . $lastDayOfMonth . ".");
		}
	}

	/**
	 * @param string $string "YYYY-MM-DD"
	 */
	public static function parse(string $string): self {
		$pieces = explode("-", $string);
		return new self($pieces[0], $pieces[1], $pieces[2]);
	}

	/**
	 * @todo consider the client's timezone. Cookie: "timezone-offset"
	 */
	public static function today(): self {
		return new self((int) date("Y"), (int) date("m"), (int) date("d"));
	}

	/**
	 * @return string "YYYY-MM-DD"
	 */
	public function __toString(): string {
		$year = str_pad((string) $this->year, 4, "0", STR_PAD_LEFT);
		$month = str_pad((string) $this->month, 2, "0", STR_PAD_LEFT);
		$day = str_pad((string) $this->day, 2, "0", STR_PAD_LEFT);
		return $year . "-" . $month . "-" . $day;
	}

	public function startOfMonth(): self {
		return new self($this->year, $this->month, 1);
	}

	public function endOfMonth(): self {
		return new self($this->year, $this->month, $this->lastDayOfMonth());
	}

	public function previousDay(): self {
		$year = $this->year;
		$month = $this->month;
		$day = $this->day;

		$day--;

		if ($day < 1) {
			$month--;

			if ($month < 1) {
				$month = 12;
				$year--;
			}

			$day = self::getLastDayOfMonth($year, $month);
		}

		return new self($year, $month, $day);
	}

	public function nextDay(): self {
		$year = $this->year;
		$month = $this->month;
		$day = $this->day;

		$day++;

		if ($day > $this->lastDayOfMonth()) {
			$month++;
			$day = 1;
		}

		if ($month > 12) {
			$month = 1;
			$year++;
		}

		return new self($year, $month, $day);
	}

	public static function getLastDayOfMonth(int $year, int $month): int {
		return match ($month) {
			1 => 31,
			2 => self::getIsLeapYear($year) ? 29 : 28,
			3 => 31,
			4 => 30,
			5 => 31,
			6 => 30,
			7 => 31,
			8 => 31,
			9 => 30,
			10 => 31,
			11 => 30,
			12 => 31,
			default => throw new \InvalidArgumentException('$month is not in range 1 to 12'),
		};
	}

	public function lastDayOfMonth(): int {
		return self::getLastDayOfMonth($this->year, $this->month);
	}

	public static function getIsLeapYear(int $year): bool {
		if ($year % 400 == 0) {
			return true;
		}
		if ($year % 100 == 0) {
			return false;
		}
		if ($year % 4 == 0) {
			return true;
		}
		return false;
	}

	public function isLeapYear(): bool {
		return self::getIsLeapYear($this->year);
	}

	public function format(bool $forOverheadPeriodPicker = false): string {
		$dateFormat = Auth::user()->date_format ?? "mdy";
		$twoDigitYear = Auth::user()->two_digit_year ?? false;
		$showDowOnTables = Auth::user()->show_dow_on_tables ?? true;
		$showDowOnPeriodPicker = Auth::user()->show_dow_on_period_picker ?? false;
		$alwaysShowYear = Auth::user()->always_show_year ?? false;

		$buffer = [];

		if (($forOverheadPeriodPicker && $showDowOnPeriodPicker) || (!$forOverheadPeriodPicker && $showDowOnTables)) {
			$buffer[] = self::DAY_OF_WEEK_NAMES[$this->dayOfWeek()];
		}

		foreach (str_split($dateFormat) as $part) {
			switch ($part) {
				case "y":
					if ($alwaysShowYear || $this->year != self::today()->year) {
						if ($twoDigitYear) {
							$buffer[] = "'" . str_pad((string) $this->year % 100, 2, "0", STR_PAD_LEFT);
						} else {
							$buffer[] = str_pad((string) $this->year, 4, "0", STR_PAD_LEFT);
						}
					}
					break;
				case "m":
					$buffer[] = self::MONTH_NAMES[$this->month];
					break;
				case "d":
					$buffer[] = (string) $this->day;
					break;
				default:
					throw new ImpossibleStateException();
			}
		}

		return implode(" ", $buffer);
	}

	public const DAY_OF_WEEK_NAMES = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

	// prettier-ignore
	public const MONTH_NAMES = [
		null,
		"Jan", "Feb", "Mar", "Apr", "May", "Jun",
		"Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
	];

	/**
	 * @return int Sun = 0, Sat = 6
	 */
	public function dayOfWeek(): int {
		return date("w", strtotime($this));
	}

	/**
	 * How many days is $that after $this?
	 * @param self $that
	 * @return int
	 */
	public function difference(self $that): int {
		return $that->julianDay() - $this->julianDay();
	}

	public function julianDay(): int {
		return unixtojd(strtotime($this));
	}

	public function daysLater(int $days): self {
		$oldTimezone = date_default_timezone_get();
		date_default_timezone_set("Etc/UTC");
		$unix = strtotime($this);
		$unix += 60 * 60 * 24 * $days;
		$string = date("Y-m-d", $unix);
		date_default_timezone_set($oldTimezone);
		return self::parse($string);
	}
}
