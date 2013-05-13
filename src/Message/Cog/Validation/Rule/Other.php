<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
*
*/
class Other implements CollectionInterface
{
	/**
	 * @param Loader $loader
	 * @return mixed|void
	 */
	public function register(Loader $loader)
	{
		$loader->registerRule('rule', array($this, 'rule'), '%s must%s pass a custom rule.');
	}

	/**
	 * @param string $var
	 * @param string $func
	 * @return mixed
	 */
	public function rule($var, $func)
	{
		return $func($var);
	}
}