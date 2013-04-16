<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

/**
* Type filters
*
* Casts a field into a specific type.
*/
class Type implements CollectionInterface
{
	protected $_defaultTimeZone = null;

	public function register(Loader $loader)
	{
		$loader->registerFilter('string',  array($this, 'string'))
			->registerFilter('int',     array($this, 'integer'))
			->registerFilter('integer', array($this, 'integer'))
			->registerFilter('float',   array($this, 'float'))
			->registerFilter('boolean', array($this, 'boolean'))
			->registerFilter('bool',    array($this, 'boolean'))
			->registerFilter('array',   array($this, 'toArray'))
			->registerFilter('arrayObject', array($this, 'toArrayObject'))
			->registerFilter('date',    array($this, 'date'))
			// Not sure why you'd want to use the following but included for completeness
			->registerFilter('null',    array($this, 'null'));
	}

	/**
	 * @param $var
	 * @return string
	 */
	public function string($var)
	{
		return (string)$var;
	}

	/**
	 * Set $round to 'up' or 'down' to force a direction $var will round, eg if you wanted to round 0.1 up to 1 you would set this to 'up'
	 *
	 * @param $var
	 * @param string $round
	 * @return int
	 */
	public function integer($var, $round = 'default')
	{
		switch ($round) {
			case 'up' :
				return ((integer) $var) + 1;
			case 'down' :
				return (integer) $var;
			default :
				return (integer) round($var);
		}
	}

	/**
	 * @param $var
	 * @return float
	 */
	public function float($var)
	{
		return (float)$var;
	}

	/**
	 * @param $var
	 * @return bool
	 */
	public function boolean($var)
	{
		return (boolean) $var;
	}

	/**
	 * @param $var
	 * @return array
	 */
	public function toArray($var)
	{
		return (array) $var;
	}

	/**
	 * @param $var
	 * @return \ArrayObject
	 */
	public function toArrayObject($var)
	{
		return new \ArrayObject((array) $var);
	}

	/**
	 * @param $var
	 * @return object
	 */
	public function object($var)
	{
		return (object) $var;
	}

	/**
	 * Attempts to turn a string, integer or array of integers into a DateTime object.
	 * 
	 * @param  mixed $var                               The variable to convert
	 * @param  \DateTimeZone | string | null $timezone  An optional timezone to use
	 * @return \DateTime				                The parsed DateTime value.
	 */
	public function date($var, $timezone = null)
	{
		$timezone = $this->_filterTimezone($timezone);

		if (is_array($var)) {
			$var = $this->_getDateFromArray($var);
		}

		if(is_numeric($var)) {
			$var = '@' . $var;
		}

		if(!$timezone) {
			$timezone = $this->_defaultTimeZone;
		}

		return new \DateTime($var, $timezone);
	}

	/**
	 * @param \DateTimeZone | string $tz
	 * @return $this
	 */
	public function setDefaultTimeZone($tz)
	{
		$tz = $this->_filterTimezone($tz);
		$this->_defaultTimeZone = $tz;

		return $this;
	}

	/**
	 * @return \DateTimeZone | null
	 */
	public function getDefaultTimeZone()
	{
		return $this->_defaultTimeZone;
	}

	/**
	 * @param $var
	 * @return null
	 */
	public function null($var)
 	{
 		return null;
 	}

	/**
	 * Checks to see if time zone is a valid data type, i.e. an instance of \DateTimeZone. If given a string it converts it to a \DateTimeZone
	 *
	 * @param mixed $tz
	 * @return \DateTimeZone | null
	 * @throws \Exception
	 */
	protected function _filterTimezone($tz)
	{
		if ($tz && !is_string($tz) && !$tz instanceof \DateTimeZone) {
			$callers = debug_backtrace();
			throw new \Exception(__CLASS__ . '::' . $callers[1]['function'] . ' - $tz must be either a string or instance of \DateTimeZone, ' . gettype($tz) . ' given');
		} elseif (is_string($tz)) {
			$tz = new \DateTimeZone($tz);
		}

		return $tz;
	}

	/**
	 * Converts an array into a valid date string.
	 *
	 * @param array $date
	 * @return string
	 */
	protected function _getDateFromArray(array $date)
	{
		foreach ($date as $value) {
			CheckType::checkNumeric($value);
		}

		$parts = array(
			'year'   => 1970,
			'month'  => 1,
			'day'    => 1,
			'hour'   => 0,
			'minute' => 0,
			'second' => 0,
		);

		$parts = array_merge($parts, $date);

		$date = $parts['year'] . '-' . $parts['month'] . '-' . $parts['day'] . ' ';
		$date .= $parts['hour'].':'.$parts['minute'].':'.$parts['second'];

		return $date;
	}

}