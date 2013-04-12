<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* Rules
*/
class Number implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerRule('min', array($this, 'min'), '%s must%s be equal to or greater than %s.')
			->registerRule('max', array($this, 'max'), '%s must%s be less than or equal to %s.')
			->registerRule('between', array($this, 'between'), '%s must%s be between %s and %s.');
	}

	public function min($var, $min)
	{
		$this->_checkNumeric($var)
			->_checkNumeric($min);

		return $var >= $min;
	}

	public function max($var, $max)
	{
		$this->_checkNumeric($var)
			->_checkNumeric($max);

		return $var <= $max;
	}

	public function between($var, $min, $max)
	{
		$this->_checkNumeric($var)
			->_checkNumeric($min)
			->_checkNumeric($max);

		if ($min >= $max) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $max must be greater than $min');
		}

		return $this->min($var, $min) && $this->max($var, $max);
	}

	public function _checkNumeric($int)
	{
		if (is_numeric($int)) {
			return $this;
		}
		$callers = debug_backtrace();
		throw new \Exception(__CLASS__ . '::' . $callers[1]['function'] . ' - $int must be a numeric value');
	}

}