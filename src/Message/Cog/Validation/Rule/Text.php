<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

/**
 * Text rule
 * @package Message\Cog\Validation\Rule
 *
 * Validating text inputs
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Text implements CollectionInterface
{
	/**
	 * Register rules to loader
	 *
	 * @param Loader $loader
	 *
	 * @return mixed|void
	 */
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

	/**
	 * Checks that a string is alpha numeric
	 *
	 * @param $var string       The variable to validate
	 *
	 * @return bool             Returns true if $var is alphanumeric
	 */
	public function alnum($var)
	{
		return ctype_alnum($var);
	}

	/**
	 * Checks that a string contains only letters
	 *
	 * @param string $var   The variable to validate
	 *
	 * @return bool         Returns true if $var is only letters
	 */
	public function alpha($var)
	{
		return ctype_alpha($var);
	}

	/**
	 * Checks that a string contains only digits
	 *
	 * @param string $var   The variable to validate
	 *
	 * @return bool         Returns true if $var is only digits
	 */
	public function digit($var)
	{
		return ctype_digit($var);
	}

	/**
	 * Checks that a string is a certain length
	 *
	 * @param string $var           The variable to validate
	 * @param int|string $min       The minimum length of $var
	 * @param null|int|string $max  The maximum length of $var. If set to null, method will check for the exact length
	 * @throws \Exception           Throws exception is $min is greater than $max
	 *
	 * @return bool                 Returns true if $var is a longer than $min and shorter than $max
	 */
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

	/**
	 * Checks that string complies to a minimum length
	 *
	 * @param string $var       The variable to validate
	 * @param int|string $min   The minimum length that $var can be
	 *
	 * @return bool             Returns true if $var is longer than $min
	 */
	public function minLength($var, $min)
	{
		CheckType::checkNumeric($min, '$min');

		$len = strlen($var);

		return $len >= $min;
	}

	/**
	 * Checks that string complies to a maximum length
	 *
	 * @param string $var       The variable to validate
	 * @param int|string $max   The maximum length that $var can be
	 *
	 * @return bool             Returns true if $var is shorter than $min
	 */
	public function maxLength($var, $max)
	{
		CheckType::checkNumeric($max, '$max');

		$len = strlen($var);

		return $len <= $max;
	}

	/**
	 * Checks that string is a valid email address
	 *
	 * @param string $var   The variable to validate
	 *
	 * @return bool         Returns true if variable is a valid email address
	 */
	public function email($var)
	{
		CheckType::checkString($var);

		return (bool) filter_var($var, \FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Checks that string is a valid url
	 *
	 * @param string $var   The variable to validate
	 *
	 * @return bool         Returns true if variable is a valid URL
	 */
	public function url($var)
	{
		CheckType::checkString($var);

		return (bool) filter_var($var, \FILTER_VALIDATE_URL);
	}

	/**
	 * Checks that string matches a regular expression pattern
	 *
	 * @param string $var       The variable to validate
	 * @param string $pattern   The regex pattern for $var to match
	 *
	 * @return bool             Returns true if a match is found
	 */
	public function match($var, $pattern)
	{
		CheckType::checkString($var);
		CheckType::checkString($pattern, '$pattern');

		return (bool) preg_match($pattern, $var);
	}

}