<?php

namespace Message\Cog\Test\Module;

use Message\Cog\Module\LocatorInterface;

class FauxLocator implements LocatorInterface
{
	protected $_mapping;

	public function __construct(array $mapping = null)
	{
		$this->_mapping = (array) $mapping;
	}

	public function getPath($moduleName)
	{
		return $this->_mapping[$moduleName];
	}
}