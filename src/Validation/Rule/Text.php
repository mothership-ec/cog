<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
 * Text rule
 * @package Message\Cog\Validation\Rule
 *
 * Validating text inputs
 *
 * @deprecated Do not use this component, use Symfony's validation component instead
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
			->registerRule('length', array($this, 'length'), '%s must%s be %s characters long.')
			->registerRule('minLength', array($this, 'minLength'), '%s must%s be at least %s characters.')
			->registerRule('maxLength', array($this, 'maxLength'), '%s must%s be no more than %s characters.')
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
	 * @param int|string $length    The length the string should have
	 *
	 * @return bool                 Returns true if $var is of length $length
	 */
	public function length($var, $length)
	{
		return strlen($var) === $length;
	}

	/**
	 * Checks that string complies to a minimum length
	 *
	 * @param string $var       The variable to validate
	 * @param int|string $min   The minimum length that $var can be
	 * @throws \Exception       Throws exception if $min is not numeric
	 *
	 * @return bool             Returns true if $var is longer than $min
	 */
	public function minLength($var, $min)
	{
		if (!is_numeric($min)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $min must be numeric');
		}
		$len = strlen($var);

		return $len >= $min;
	}

	/**
	 * Checks that string complies to a maximum length
	 *
	 * @param string $var       The variable to validate
	 * @param int|string $max   The maximum length that $var can be
	 * @throws \Exception       Throws exception if $max is not numeric
	 *
	 * @return bool             Returns true if $var is shorter than $min
	 */
	public function maxLength($var, $max)
	{
		if (!is_numeric($max)) {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $max must be numeric');
		}
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
		return (bool) preg_match($pattern, $var);
	}

}