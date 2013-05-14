<?php

namespace Message\Cog\ValueObject;

use DateTime;
use DateInterval;

/**
 * Represents a date range: the period between two specific timestamps.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Danny Hannah <danny@message.co.uk>
 */
class DateRange
{
	protected $_start;
	protected $_end;

	/**
	 * Constructor.
	 *
	 * @param DateTime|null $from Starting timestamp, null for infinite past
	 * @param DateTime|null $to   Ending timestamp, null for infinite future
	 */
	public function __construct(DateTime $from = null, DateTime $to = null)
	{

	}

	/**
	 * Check whether a given date & time falls within the date range.
	 *
	 * @param  DateTime|null  $datetime The date & time to check, null for
	 *                                  current date & time
	 *
	 * @return boolean                  True if the date & time is in the range
	 */
	public function isInRange(DateTime $datetime = null)
	{

	}

	/**
	 * Get the period between a given date & time and the start of the date
	 * range, represented as an instance of `DateInterval`.
	 *
	 * @param  DateTime|null  $datetime The date & time to use, null for
	 *                                  current date & time
	 *
	 * @return DateInterval             The interval between the supplied date &
	 *                                  time and the start of this date range
	 */
	public function getIntervalToStart(DateTime $datetime = null)
	{

	}

	/**
	 * Get the period between a given date & time and the end of the date
	 * range, represented as an instance of `DateInterval`.
	 *
	 * @param  DateTime|null  $datetime The date & time to use, null for
	 *                                  current date & time
	 *
	 * @return DateInterval             The interval between the supplied date &
	 *                                  time and the end of this date range
	 */
	public function getIntervalToEnd(DateTime $datetime = null)
	{

	}

	/**
	 * Output the date range as a string.
	 *
	 * @return string The date range represented as a string
	 */
	public function __toString()
	{

	}
}