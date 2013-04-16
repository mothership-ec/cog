<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
* 
*/
class Other implements CollectionInterface
{
	protected $_validator = null;

	/**
	 * @param Loader $loader
	 * @return mixed|void
	 */
	public function register(Loader $loader)
	{
		// Save the validator so that we can access it's data at a later time.
		$this->_validator = $loader->getValidator();
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