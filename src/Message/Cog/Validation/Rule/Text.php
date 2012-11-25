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
		$loader->registerRule('alpha', array($this, 'alpha'), '%s must%s be alphabetical.');
		$loader->registerRule('digit', array($this, 'digit'), '%s must%s only contain digits.');
		$loader->registerRule('length', array($this, 'length'), '%s must%s be between %s and %s characters.');
		$loader->registerRule('email', array($this, 'email'), '%s must%s be a valid email address.');
		$loader->registerRule('url', array($this, 'url'), '%s must%s be a valid URL.');
		$loader->registerRule('match', array($this, 'match'), '%s must%s match the regular expression %s.');
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

	public function url($var)
	{
		return filter_var($var, \FILTER_VALIDATE_URL);
	}

	public function match($var, $pattern)
	{
		return preg_match($var, $pattern);
	}
}