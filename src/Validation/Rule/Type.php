<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
 * Date rule
 * @package Message\Cog\Validation\Rule
 *
 * Class used to validating and comparing types.
 *
 * @deprecated Do not use this component, use Symfony's validation component instead
 */
class Type implements CollectionInterface
{
	/**
	 * Register rules to Loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('number', array($this, 'number'), '%s must%s be numeric.');
	}

	/**
	 * Checks that the given $var is a number or a numeric string
	 *
	 * @param $var mixed 	The variable to validate
	 *
	 * @return bool 		Returns true if $var is numeric
	 */
	public function number($var)
	{
		return is_numeric($var);
	}
}