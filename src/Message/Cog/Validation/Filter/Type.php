<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;

/**
* Filters
*/
class Type implements CollectionInterface
{
	protected $_defaultTimeZone = null;

	public function register($loader)
	{
		$loader->registerFilter('string',  array($this, 'string'));
		$loader->registerFilter('int',     array($this, 'integer'));
		$loader->registerFilter('integer', array($this, 'integer'));
		$loader->registerFilter('float',   array($this, 'float'));
		$loader->registerFilter('boolean', array($this, 'boolean'));
		$loader->registerFilter('bool',    array($this, 'boolean'));
		$loader->registerFilter('array',   array($this, 'toArray'));
		$loader->registerFilter('object',  array($this, 'object'));
		$loader->registerFilter('date',    array($this, 'date'));
		// Not sure why you'd want to use the following but included for completeness
		$loader->registerFilter('null',    array($this, 'null')); 
	}

	public function string($var)
	{
		return (string)$var;
	}

	public function integer($var)
	{
		return (integer)$var;
	}

	public function float($var)
	{
		return (float)$var;
	}

	public function boolean($var)
	{
		return (boolean)$var;
	}

	public function toArray($var)
	{
		return (array)$var;
	}

	public function object($var)
	{
		return (object)$var;
	}

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

	public function null($var)
	{
		return null;
	}

	public function setDefaultTimeZone(\DateTimeZone $tz)
	{
		$this->_defaultTimeZone = $tz;
	}
}