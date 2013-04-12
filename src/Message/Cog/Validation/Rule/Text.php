<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* Rules
*/
class Text implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerRule('alnum', array($this, 'alnum'), '%s must%s be alphanumeric.')
			->registerRule('alpha', array($this, 'alpha'), '%s must%s be alphabetical.')
			->registerRule('digit', array($this, 'digit'), '%s must%s only contain digits.')
			->registerRule('length', array($this, 'length'), '%s must%s be between %s and %s characters.')
			->registerRule('email', array($this, 'email'), '%s must%s be a valid email address.')
			->registerRule('url', array($this, 'url'), '%s must%s be a valid URL.')
			->registerRule('match', array($this, 'match'), '%s must%s match the regular expression %s.');
	}

	public function alnum($var)
	{
		return ctype_alnum($var);
	}

	public function alpha($var)
	{
		return ctype_alpha($var);
	}

	public function digit($var)
	{
		return ctype_digit($var);
	}

	public function length($var, $min, $max = null)
	{
		$len = strlen($var);

		$this->_checkNumeric($min);

		// Overloaded option if one param is specified, checks exact length
		if ($max === null) {
			return $len === $min;
		} elseif ($min >= $max) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $max must be greater than $min');
		}

		$this->_checkNumeric($max);

		return ($len >= $min && $len <= $max);
	}

	public function minLength($var, $min)
	{
		$this->_checkNumeric($min);

		$len = strlen($var);

		return $len >= $min;
	}

	public function maxLength($var, $max)
	{
		$this->_checkNumeric($max);

		$len = strlen($var);

		return $len <= $max;
	}

	public function email($var)
	{
		$this->_checkString($var);
		return (bool) filter_var($var, \FILTER_VALIDATE_EMAIL);
	}

	public function url($var)
	{
		$this->_checkString($var);
		return (bool) filter_var($var, \FILTER_VALIDATE_URL);
	}

	public function match($var, $pattern)
	{
		$this->_checkString($var);
		return preg_match($var, $pattern);
	}

	protected function _checkNumeric($int)
	{
		if (!is_numeric($int)) {
			$callers = debug_backtrace();
			throw new \Exception(__CLASS__ . '::' . $callers[1]['function'] . ' - $max must be numeric if set');
		}

		return $this;
	}

	protected function _checkString($string)
	{
		if (is_int($string)) {
			$string = (string) $string;
		}

		if (!is_string($string)) {
			$callers = debug_backtrace();
			throw new \Exception(__CLASS__ . '::' . $callers[1]['function'] . ' - $string param must be a string, ' . gettype($string) . ' given');
		}

		return $this;
	}

}