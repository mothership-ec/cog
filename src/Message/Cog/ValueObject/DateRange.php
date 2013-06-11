<?php

namespace Message\Cog\ValueObject;

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
	 * @param DateTimeImmutable|null $from Starting timestamp, null for infinite past
	 * @param DateTimeImmutable|null $to   Ending timestamp, null for infinite future
	 */
	public function __construct(DateTimeImmutable $from = null, DateTimeImmutable $to = null)
	{
		if (!$from && !$to) {
			throw new \LogicException('Date range could not be instantiated: at least one date must be provided');
		}

		$this->_end   = $to;
		$this->_start = $from;
	}

	/**
	 * Check whether a given date & time falls within the date range.
	 *
	 * @param DateTimeImmutable|null  $datetime The date & time to check, null for
	 *                                 current date & time
	 *
	 * @return boolean                 True if the date & time is in the range
	 */
	public function isInRange(DateTimeImmutable $datetime = null)
	{
		if (!$datetime) {
			$datetime = new DateTimeImmutable;
		}

		// If there is not a start date, ensure that the given timestamp is less than
		// or equal to the end date
		if (!$this->_start) {
			return $datetime->getTimestamp() <= $this->_end->getTimestamp();
		}

		// If there is not an end date, then ensure that the given timestamp is greater
		// or equal to the start date
		if (!$this->_end) {
			return $datetime->getTimestamp() >= $this->_start->getTimestamp();
		}

		return $datetime->getTimestamp() >= $this->_start->getTimestamp() && $datetime->getTimestamp() <= $this->_end->getTimestamp();
	}

	/**
	 * Get the period between a given date & time and the start of the date
	 * range, represented as an instance of `DateInterval`.
	 *
	 * @param DateTimeImmutable|null  $datetime The date & time to use, null for
	 *                                 current date & time
	 *
	 * @return DateInterval            The interval between the supplied date &
	 *                                 time and the start of this date range
	 */
	public function getIntervalToStart(DateTimeImmutable $datetime = null)
	{
		if (!$datetime) {
			$datetime = new DateTimeImmutable;
		}

		if (!$this->_start) {
			throw new \LogicException('A start date must be provided');
		}

		return $datetime->diff($this->_start);
	}

	/**
	 * Get the period between a given date & time and the end of the date
	 * range, represented as an instance of `DateInterval`.
	 *
	 * @param DateTime|null  $datetime The date & time to use, null for
	 *                                 current date & time
	 *
	 * @return DateInterval            The interval between the supplied date &
	 *                                 time and the end of this date range
	 */
	public function getIntervalToEnd(DateTimeImmutable $datetime = null)
	{
		if (!$datetime) {
			$datetime = new DateTimeImmutable;
		}

		if (!$this->_end) {
			throw new \LogicException('An end date must be provided');
		}

		return $datetime->diff($this->_end);
	}

	/**
	 * return the $_start DateTime object
	 *
	 * @return DateTime|false start time
	 */
	public function getStart()
	{
		return $this->_start;
	}

	/**
	 * return the $_end DateTime object
	 *
	 * @return DateTime|false end time
	 */
	public function getEnd()
	{
		return $this->_end;
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