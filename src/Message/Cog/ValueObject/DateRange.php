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
		if (!$from && !$to) {
			throw new \LogicException('$from or $to must be provided');
		}
	
		$this->_end = $to;
		$this->_start = $from;
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
		if (!$datetime) {
			$datetime = new DateTime;
		}
		
		// If there is not a start date, ensure that the given timestamp is less than
		// or equal to the end date
		if (!$this->_start) {
			return ($datetime->getTimestamp() <= $this->_end->getTimestamp());
		}
		
		// If there is not an end date, then esnure that the given timestamp is greater
		// or equal to the start date
		if (!$this->_end) {
			return ($datetime->getTimestamp() >= $this->_start->getTimestamp());
		}

		return ($datetime->getTimestamp() >= $this->_start->getTimestamp() && $datetime->getTimestamp() <= $this->_end->getTimestamp());
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
		if (!$datetime) {
			$datetime = new DateTime;
		}
		
		if (!$this->_start) {
			throw new \LogicException('A to date must be provided');
		}
		
		return $datetime->diff($this->_start);
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
		if (!$datetime) {
			$datetime = new DateTime;
		}

		if (!$this->_end) {
			throw new \LogicException('A from date must be provided');
		}

		return $datetime->diff($this->_end);
	}

	/**
	 * Output the date range as a string.
	 *
	 * @return string The date range represented as a string
	 */
	public function __toString()
	{
		return $this->_start->format('c').' - '.$this->_end->format('c');
	}
}