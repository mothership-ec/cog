<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
 * Other rule
 * @package Message\Cog\Validation\Rule
 *
 * Can use callables such as native PHP functions to validate inputs
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Other implements CollectionInterface
{
	/**
	 * Register rules to loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('rule', array($this, 'rule'), '%s must%s pass a custom rule.');
	}

	/**
	 * @param string $var       The variable to validate
	 * @param string $func      A callable function to use to validate $var
	 * @return mixed
	 */
	public function rule($var, $func)
	{
		return $func($var);
	}
}