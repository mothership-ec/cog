<?php

namespace Message\Cog\Helper;

/**
 * Date helper methods.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class DateHelper
{
	/**
	 * Attempt to detect the format of a date. Prefers UK / international dates
	 * over U.S. dates.
	 *
	 * @param  string    $date
	 * @throws Exception Could not determine date format of $date
	 * @return string    Format of date
	 */
	public function detectFormat($date)
	{
		// Get the split character
		if (false !== strstr($date, '/')) $split = '/';
		if (false !== strstr($date, '-')) $split = '-';

		// Check if the date starts with dd or mm
		if (preg_match('/[0-9]{2}(\/|-)[0-9]{2}(\/|-)[0-9]{4}/', $date)) {
			$parts = explode($split, $date);

			// Do a dumb check to see if the second value is greater than 12
			// then it must be a dd instead of mm, and thus mm/dd/yyyy
			if ($parts[1] > 12) {
				return 'm'.$split.'d'.$split.'Y';
			}

			return 'd'.$split.'m'.$split.'Y';
		}
		// Else check if the date starts with yyyy
		elseif (preg_match('/[0-9]{4}(\/|-)[0-9]{2}(\/|-)[0-9]{2}/', $date)) {
			return 'Y'.$split.'m'.$split.'d';
		}

		throw new \Exception(sprintf("Could not determine date format of '%s'", $date));
	}

}