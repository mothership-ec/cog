<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;

/**
* Filters
*/
class Type implements CollectionInterface
{
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

	public function date($var, $timezone = null)
	{
		return new \DateTime($var, $timezone);
	}

	public function null($var)
	{
		return null;
	}
}