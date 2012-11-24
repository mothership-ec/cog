<?php

namespace Message\Cog\Validation\Filter;

use Message\Cog\Validation\CollectionInterface;

/**
* Filters
*/
class Other implements CollectionInterface
{
	public function register($loader)
	{
		$loader->registerFilter('filter',  array($this, 'filter'));
	}

	public function filter($var, $func)
	{
		return $func($var);
	}
}