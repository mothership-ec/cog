<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
 * Type filters.
 * @package Message\Cog\Validation\Filter
 *
 * Casts fields to specific data types.
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Type implements CollectionInterface
{
	protected $_defaultTimeZone = null;

	/**
	 * Register the filters to the validation loader.
	 *
	 * @param Loader $loader The validation loader instance
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader
			->registerFilter('string',      array($this, 'string'))
			->registerFilter('int',         array($this, 'integer'))
			->registerFilter('integer',     array($this, 'integer'))
			->registerFilter('float',       array($this, 'float'))
			->registerFilter('boolean',     array($this, 'boolean'))
			->registerFilter('bool',        array($this, 'boolean'))
			->registerFilter('array',       array($this, 'toArray'))
			->registerFilter('object',      array($this, 'object'))
			->registerFilter('date',        array($this, 'date'))
			->registerFilter('null',        array($this, 'null'));
	}

	/**
	 * Cast a field to a string.
	 *
	 * @param mixed $var The variable to cast
	 *
	 * @return string    The field calue cast to a string
	 */
	public function string($var)
	{
		return (string) $var;
	}

	/**
	 * Cast a field to an integer.
	 *
	 * Supports rounding control using the second parameter `$round`.
	 *
	 * Set $round to 'up' or 'down' to force a direction $var will round, eg if
	 * you wanted to round 0.1 up to 1 you would set this to 'up'.
	 *
	 * @param mixed $var    The variable to cast
	 * @param string $round Rounding rule. Possible values: 'up', 'down' & 'default'
	 *
	 * @return int          The field value cast to an integer
	 */
	public function integer($var, $round = 'default')
	{
		switch ($round) {
			case 'up':
				return ((int) $var) + 1;
			case 'down':
				return (int) $var;
			default:
				return (int) round($var);
		}
	}

	/**
	 * Cast a field to a float.
	 *
	 * @param mixed $var The variable to cast
	 *
	 * @return float     The field value cast to a float
	 */
	public function float($var)
	{
		return (float) $var;
	}

	/**
	 * Cast a field to a boolean.
	 *
	 * @param mixed $var The variable to cast
	 *
	 * @return bool      The field value cast to a boolean
	 */
	public function boolean($var)
	{
		return (boolean) $var;
	}

	/**
	 * Cast a field to an array.
	 *
	 * @param mixed $var The variable to cast
	 *
	 * @return array     The field value cast to an array
	 */
	public function toArray($var)
	{
		return (array) $var;
	}

	/**
	 * Cast a field to a `stdClass` object.
	 *
	 * @param mixed $var    The variable to cast
	 *
	 * @return \stdClass    The value cast to an object.
	 */
	public function object($var)
	{
		return (object) $var;
	}

	/**
	 * Attempts to turn a string, integer or array of integers into a `DateTime`
	 * object.
	 *
	 * @param  mixed $var                          The variable to convert
	 * @param  \DateTimeZone|string|null $timezone An optional timezone to use
	 *
	 * @return \DateTime				           The parsed DateTime instance
	 */
	public function date($var, $timezone = null)
	{
		$timezone = $this->_filterTimezone($timezone);

		if (is_array($var)) {
			$var = $this->_getDateFromArray($var);
		}

		if (is_numeric($var)) {
			$var = '@' . $var;
		}

		if (!$timezone) {
			$timezone = $this->_defaultTimeZone;
		}

		return new \DateTime($var, $timezone);
	}

	/**
	 * Set the default time zone to use for
	 *
	 * @param \DateTimeZone|string $tz A `DateTimeZone` instance or a string
	 *                                 representing the timezone
	 *
	 * @return Type                    Returns $this for chainability
	 */
	public function setDefaultTimeZone($tz)
	{
		$tz = $this->_filterTimezone($tz);
		$this->_defaultTimeZone = $tz;

		return $this;
	}

	/**
	 * Get the default time zone set on this instance.
	 *
	 * @return \DateTimeZone|null The default time zone, or `null` if not set
	 */
	public function getDefaultTimeZone()
	{
		return $this->_defaultTimeZone;
	}

	/**
	 * Casts a variable to `null`. This method always returns `null`.
	 *
	 * @param mixed $var The variable to cast
	 *
	 * @return null
	 */
	public function null($var)
 	{
 		return null;
 	}

	/**
	 * Turns a string representing a timezone (i.e. 'Europe/London') into an
	 * instance of `DateTimeZone`.
	 *
	 * @param \DateTimeZone|string $tz      A `DateTimeZone` instance or a string
	 *                                      representing the timezone
	 *
	 * @return \DateTimeZone                The timezone as an instance of `DateTimeZone`
	 *
	 * @throws \InvalidArgumentException    Throws exception if the input variable was neither a
	 *                                      string nor an instance of `DateTimeZone`
	 */
	protected function _filterTimezone($tz)
	{
		if ($tz && !is_string($tz) && !$tz instanceof \DateTimeZone) {
			$callers = debug_backtrace();
			throw new \InvalidArgumentException(sprintf(
				'%s: $tz must be either a string or instance of \DateTimeZone, `%s` given',
				__CLASS__ . '::' . $callers[1]['function'],
				gettype($tz)
			));
		}
		elseif (is_string($tz)) {
			$tz = new \DateTimeZone($tz);
		}

		return $tz;
	}

	/**
	 * Converts an array into a valid date string.
	 *
	 * @param array $date       Date as an array, to be merged with defaults to create a complete date string
	 * @throws \Exception       Throws exception if there are any invalid date keys
	 *
	 * @return string
	 */
	protected function _getDateFromArray(array $date)
	{
		$parts = array(
			'year'   => 1970,
			'month'  => 1,
			'day'    => 1,
			'hour'   => 0,
			'minute' => 0,
			'second' => 0,
		);

		foreach ($date as $key => $value) {
			if (!array_key_exists($key, $parts)) {
				throw new \Exception(__CLASS__ . '::' . __METHOD__ . " - '" . $key . " is not a valid date part");
			}
		}

		$parts = array_merge($parts, $date);

		$date = $parts['year'] . '-' . $parts['month'] . '-' . $parts['day'] . ' ';
		$date .= $parts['hour'].':'.$parts['minute'].':'.$parts['second'];

		return $date;
	}

}