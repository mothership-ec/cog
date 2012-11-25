<?php

namespace Message\Cog\Validation\Rule;

use Message\Cog\Validation\CollectionInterface;

/**
* 
*/
class Other implements CollectionInterface
{
	protected $_validator = null;

	public function register($loader)
	{
		// Save the validator so that we can access it's data at a later time.
		$this->_validator = $loader->getValidator();
		$loader->registerRule('rule', array($this, 'rule'), '%s must%s pass a custom rule.');
	}

	public function rule($func)
	{
		return $func($var, $this->_validator->getData());
	}
}