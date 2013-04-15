<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

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

		CheckType::checkNumeric($min, '$min');

		// Overloaded option if one param is specified, checks exact length
		if ($max === null) {
			return $len === $min;
		} elseif ($min >= $max) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $max must be greater than $min');
		}

		CheckType::checkNumeric($max, '$max');

		return ($len >= $min && $len <= $max);
	}

	public function minLength($var, $min)
	{
		CheckType::checkNumeric($min, '$min');

		$len = strlen($var);

		return $len >= $min;
	}

	public function maxLength($var, $max)
	{
		CheckType::checkNumeric($max, '$max');

		$len = strlen($var);

		return $len <= $max;
	}

	public function email($var)
	{
		CheckType::checkString($var);
		return (bool) filter_var($var, \FILTER_VALIDATE_EMAIL);
	}

	public function url($var)
	{
		CheckType::checkString($var);
		return (bool) filter_var($var, \FILTER_VALIDATE_URL);
	}

	public function match($var, $pattern)
	{
		CheckType::checkString($var);
		return preg_match($var, $pattern);
	}

}