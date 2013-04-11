<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

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
			->registerFilter('\ArrayObject', array($this, 'toArrayObject'))
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
	 * @param  mixed		$var      The variable to convert
	 * @param  \DateTimeZone $timezone An optional timezone to use
	 * @param  array   		$keys     If $var is an array can be used to denote what values
	 *                 		          should be keys.
	 * @return \DateTime				  The parsed DateTime value.
	 */
	public function date($var, \DateTimeZone $timezone = null, array $keys = array())
	{
		if(is_array($var)) {

			$parts = array(
				'year'   => 1970, 
				'month'  => 1, 
				'day'    => 1,
				'hour'   => 0,
				'minute' => 0,
				'second' => 0,
			);

			foreach($parts as $part => &$value) {
				if(isset($var[$part])) {
					$value = (int)$var[$part];
				}
			}

			$var = $parts['year'].'-'.$parts['month'].'-'.$parts['day'].' ';
			$var.= $parts['hour'].':'.$parts['minute'].':'.$parts['second'];
		}

		if(is_int($var)) {
			$var = '@'.$var;
		}

		if(!$timezone) {
			$timezone = $this->_defaultTimeZone;
		}

		return new \DateTime($var, $timezone);
	}

	/**
	 * @param \DateTimeZone $tz
	 * @return $this
	 */
	public function setDefaultTimeZone(\DateTimeZone $tz)
	{
		$this->_defaultTimeZone = $tz;

		return $this;
	}

	/**
	 * @param $var
	 * @return null
	 */
	public function null($var)
 	{
 		return null;
 	}
}	