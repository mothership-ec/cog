<?php

namespace Message\Cog\Bootstrap;

/**
 * Bootstrap loader interface for loadinging bootstraps from modules or Cog
 * itself.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LoaderInterface
{
	/**
	 * Load all bootstrap files from a given directory.
	 *
	 * @param  string $path      The directory to load from
	 * @param  string $namespace The namespace for this directory
	 *
	 * @return Loader            Returns $this for chaining
	 */
	public function addFromDirectory($path, $namespace);

	/**
	 * Adds a bootstrap this loader.
	 *
	 * @param  BootstrapInterface $bootstrap The bootstrap to add
	 *
	 * @return Loader                        Returns $this for chaining
	 */
	public function add(BootstrapInterface $bootstrap);

	/**
	 * Load all of the bootstraps that have been added to this loader.
	 */
	public function load();

	/**
	 * Clear all bootstraps from this loader.
	 *
	 * @return Loader Returns $this for chaining
	 */
	public function clear();
}