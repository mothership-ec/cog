<?php

namespace Message\Cog\Module;

/**
 * Interface for the module loader, responsible for loading Cog modules and
 * running their bootstraps.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LoaderInterface
{
	/**
	 * Validate & load a list of modules, in order.
	 *
	 * @param array $modules List of module names to load, e.g. `Message\Raven`
	 */
	public function run(array $modules);

	/**
	 * Check if a given module was requested to be loaded within this
	 * application.
	 *
	 * @param  string $name Module name to check for
	 * @return boolean      Result of the check
	 */
	public function exists($name);

	/**
	 * Get list of modules that this application has requested are loaded.
	 *
	 * @return array Array of modules
	 */
	public function getModules();
}