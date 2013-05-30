<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
 * Other filter
 * @package Message\Cog\Validation\Filter
 *
 * Parse variables through callables such as native PHP functions
 *
 * @author James Moss <james@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Other implements CollectionInterface
{
	/**
	 * Register filters to loader
	 *
	 * @param Loader $loader
	 *
	 * @return void
	 */
	public function register(Loader $loader)
	{
		$loader->registerFilter('filter',  array($this, 'filter'));
	}

	/**
	 * @param mixed $var    Variable to be filtered
	 * @param string $func  Function used to filter $var
	 * @throws \Exception   Throws exception is $func is not callable
	 *
	 * @return mixed
	 */
	public function filter($var, $func)
	{
		if (is_callable($func)) {
			return call_user_func($func, $var);
		}
		else {
			throw new \Exception(__CLASS__ . '::' . __METHOD__ . ' - $func must be callable');
		}
	}
}