<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Check\Type as CheckType;

/**
* Filters
 *
 * Use this method for running through other callables, such as native PHP functions
*/
class Other implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerFilter('filter',  array($this, 'filter'));
	}

	/**
	 * @param $var
	 * @param string $func
	 * @return mixed
	 */
	public function filter($var, $func)
	{
		CheckType::checkString($func);
		return $func($var);
	}
}