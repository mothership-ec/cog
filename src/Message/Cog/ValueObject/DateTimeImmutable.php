<?php

namespace Message\Cog\ValueObject;

use DateTime;
use DateTimeZone;
use DateInterval;

/**
 * Immutable DateTime object.
 *
 * This works the same as `\DateTime`, but will return a *new* instance of
 * itself when any modifying methods are called, rather than modifying the
 * current instance.
 *
 * This is a patch for the core `\DateTimeImmutable` object which will be
 * available in PHP 5.5.
 *
 * @see http://www.php.net/manual/en/class.datetimeimmutable.php
 * @see https://gist.github.com/lstrojny/1747838
 */
class DateTimeImmutable extends DateTime
{
	protected $_frozen = true;

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.createfromformat.php
	 */
	public static function createFromFormat($format, $time, $timezone = null)
	{
		$date = ($timezone)
				? parent::createFromFormat($format, $time, $timezone)
				: parent::createFromFormat($format, $time);

		if (false === $date) {
			return false;
		}

		return new static($date->format(date('c', $date->getTimestamp())), $timezone);
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.modify.php
	 */
	public function modify($dateString)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.add.php
	 */
	public function add($interval)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.sub.php
	 */
	public function sub($interval)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.settimezone.php
	 */
	public function setTimezone($timezone)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.settime.php
	 */
	public function setTime($hour, $minute, $second = null)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.setdate.php
	 */
	public function setDate($year, $month, $day)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.setisodate.php
	 */
	public function setISODate($year, $week, $day = 1)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * @see http://www.php.net/manual/en/datetimeimmutable.settimestamp.php
	 */
	public function setTimestamp($timestamp)
	{
		return $this->_guard(__FUNCTION__, func_get_args());
	}

	/**
	 * Run a given modifying method on the parent object and return the new
	 * instance of `DateTimeImmutable` and not modify the current instance.
	 *
	 * This works by cloning the current instance and changing the `_frozen`
	 * property which this method checks and runs the method on the parent when
	 * it is false (working off the clone, not the current instance).
	 *
	 * @param  string $method    The method to call
	 * @param  array  $arguments The arguments to use for the method call
	 *
	 * @return DateTimeImmutable The new instance with the new date/time
	 */
	protected function _guard($method, array $arguments)
	{
		// If this is the original call, freeze this instance and call the method again
		if ($this->_frozen) {
			$date = clone $this;
			$date->_frozen = false;
			call_user_func_array(array($date, $method), $arguments);
			$date->_frozen = true;
			return $date;
		}
		// Otherwise, call the parent (DateTime) method and return it
		else {
			return call_user_func_array(array('parent', $method), $arguments);
		}
	}
}