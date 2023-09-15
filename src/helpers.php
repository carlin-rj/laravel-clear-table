<?php

use Illuminate\Support\Carbon;

if (!function_exists('generateDateRanges')) {
	function generateDateRanges(int $interval = 1, string $unit = 'month', ?Carbon $start = null, ?Carbon $end = null, string $format = 'Y-m-d H:i:s'): array
	{
		// ... 你的函数代码
		$start = $start ?? now()->subYears(3);
		$end = $end ?? now();
		$ranges = [];

		//@phpstan-ignore-next-line
		while ($start < $end) {
			$next = clone $start;

			switch ($unit) {
				case 'second':
					$next->addSeconds($interval);
					break;
				case 'minute':
					$next->addMinutes($interval);
					break;
				case 'hour':
					$next->addHours($interval);
					break;
				case 'day':
					$next->addDays($interval);
					break;
				case 'month':
				default:
					$next->addMonths($interval);
			}

			// Adjust the next value if it exceeds the end.
			if ($next > $end) {
				break;  // Exit the loop immediately
			}

			$ranges[] = [
				'start' => $start->format($format),
				'end'   => $next->format($format),
			];

			$start = $next;
		}

		return $ranges;
	}
}
