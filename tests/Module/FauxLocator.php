<?php

namespace Message\Cog\Test\Module;

use Message\Cog\Module\LocatorInterface;

/**
 * A simple module locator implementation for use when testing.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxLocator implements LocatorInterface
{
	protected $_mapping;

	/**
	 * Constructor.
	 *
	 * Allows a map of modules and their location to be passed which will be
	 * returned where requested in `getPath`.
	 *
	 * @param array|null $mapping Array where module name is the key and the
	 *                            location is the value.
	 */
	public function __construct(array $mapping = null)
	{
		$this->_mapping = (array) $mapping;
	}

	/**
	 * Get the path to a given module.
	 *
	 * This returns the path defined as the value for the given key for the
	 * first parameter of `__construct`.
	 *
	 * @param  string $moduleName The module name to find the path for
	 * @return string             The path to this module
	 */
	public function getPath($moduleName)
	{
		return $this->_mapping[$moduleName];
	}
}