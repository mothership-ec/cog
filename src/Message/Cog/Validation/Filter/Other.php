<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* Filters
*/
class Other implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerFilter('filter',  array($this, 'filter'));
	}

	/**
	 * @param $var
	 * @param $func
	 * @return mixed
	 */
	public function filter($var, $func)
	{
		return $func($var);
	}
}