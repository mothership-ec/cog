<?php

namespace Message\Cog\Test\Module;

use Message\Cog\Module\LoaderInterface;

/**
 * A simple implementation of `Module\LoaderInterface` used for unit testing.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxLoader implements LoaderInterface
{
	protected $_modules = array();

	/**
	 * Loads a list of modules.
	 *
	 * This just adds the module names to an internal list of modules that have
	 * been loaded. Nothing is actually loaded, this class is for testing
	 * purposes only.
	 *
	 * @param array $modules List of modules to load
	 */
	public function run(array $modules)
	{
		$this->_modules = array_merge($this->_modules, $modules);
	}

	/**
	 * Checks if a given module has been "loaded" by this class.
	 *
	 * @param string $name Full module name
	 */
	public function exists($name)
	{
		return (false !== array_search($name, $this->_modules));
	}

	/**
	 * Get all modules that have been "loaded" by this class.
	 *
	 * @return array All modules
	 */
	public function getModules()
	{
		return $this->_modules;
	}
}