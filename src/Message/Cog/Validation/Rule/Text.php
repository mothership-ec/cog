<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;

/**
* Rules
*/
class Text implements CollectionInterface
{
	public function register($loader)
	{
		$loader->registerRule('alnum', array($this, 'alnum'), '%s must%s be alphanumeric.');
		$loader->registerRule('length', array($this, 'length'), '%s must%s be between %s and %s characters.');
		$loader->registerRule('email', array($this, 'email'), '%s must%s be a valid email address');
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

		// Overloaded option if one param is specified, checks exact length
		if($max === null) {
			return $len === $min;
		}

		return ($len >= $min && $len <= $max);
	}

	public function email($var)
	{
		return filter_var($var, \FILTER_VALIDATE_EMAIL);
	}
}