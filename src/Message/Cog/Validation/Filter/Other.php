<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

/**
 * Other filter
 *
 * Parse variables through callables such as native PHP functions
 */
class Other implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerFilter('filter',  array($this, 'filter'));
	}

	/**
	 * @param $var          Variable to be filtered
	 * @param string $func  Function used to filter $var
	 *
	 * @return mixed
	 */
	public function filter($var, $func)
	{
		CheckType::checkString($func);
		return $func($var);
	}
}