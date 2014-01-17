<?php

namespace Message\Cog\Bootstrap;

use Message\Cog\Filesystem\Finder;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\HTTP\RequestAwareInterface;

/**
 * Bootstrap loader, responsible for loading bootstraps from modules or Cog
 * itself.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements LoaderInterface
{
	protected $_services;
	protected $_finder;
	protected $_bootstraps = array();

	protected $_cacheEnabled = false;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $container The service container
	 */
	public function __construct(ContainerInterface $container, Finder $finder)
	{
		$this->_services = $container;
		$this->_finder   = $finder;
	}

	/**
	 * Load all bootstrap files from a given directory. This does *not* support
	 * recursive loading.
	 *
	 * This will add any classes in the directory that extend the
	 * `BootstrapInterface` interface to this loader.
	 *
	 * @param  string $path      The directory to load from
	 * @param  string $namespace The namespace for this directory
	 *
	 * @return Loader            Returns $this for chaining
	 */
	public function addFromDirectory($path, $namespace)
	{
		if (! file_exists($path) or ! is_dir($path)) {
			return $this;
		}

		// Check the leading namespace slash is there
		if ('\\' !== substr($namespace, 0, 1)) {
			$namespace = '\\' . $namespace;
		}

		$cache = $this->_services['cache'];
		$cacheKey = sprintf('cog.bootstrap.loader.%s.classNames', str_replace('\\', '_', $namespace));

		// Get the class names from the path and cache if not on local
		if (false === $this->_cacheEnabled or (false === $classNames = $cache->fetch($cacheKey))) {
			$classNames = array();

			// Find all files in the path recursively
			$finder = $this->_finder->files()->in($path);

			foreach ($finder as $file) {
				// Skip non-php files
				if ('php' !== $file->getExtension()) {
					continue;
				}
				// Determine class name
				$className = $namespace . str_replace('/', '\\', str_replace($path, '', $file->getPath())) . '\\' . $file->getBasename('.php');
				// Check class can be loaded, skip if not
				if (!class_exists($className)) {
					continue;
				}

				$classNames[] = $className;
			}

			$cache->store($cacheKey, $classNames);
		}

		foreach ($classNames as $className) {
			// Load the bootstrap
			$class = new $className;
			if ($class instanceof ContainerAwareInterface) {
				$class->setContainer($this->_services);
			}
			if ($class instanceof RequestAwareInterface) {
				$class->setRequest($this->_services['request']);
			}
			// Add to the internal list if it implements `BootstrapInterface`
			if ($class instanceof BootstrapInterface) {
				$this->add($class);
			}
		}

		return $this;
	}

	/**
	 * Adds a bootstrap this loader.
	 *
	 * @param  BootstrapInterface $bootstrap The bootstrap to add
	 *
	 * @return Loader                        Returns $this for chaining
	 */
	public function add(BootstrapInterface $bootstrap)
	{
		$this->_bootstraps[] = $bootstrap;

		return $this;
	}

	/**
	 * Load all of the bootstraps that have been added to this loader.
	 *
	 * This loads all of the bootstraps that extend `ServicesInterface` first,
	 * because these are often used by event, route or task definitions.
	 *
	 * Routes are loaded second, then events and tasks are registered
	 * simultaneously after this.
	 *
	 * Once complete, all the bootstraps are removed from this loader as they
	 * have now been loaded.
	 */
	public function load()
	{
		// Register services first
		foreach ($this->_bootstraps as $bootstrap) {
			if ($bootstrap instanceof ServicesInterface) {
				$bootstrap->registerServices($this->_services);
			}
		}

		// Register routes second
		foreach ($this->_bootstraps as $bootstrap) {
			if ($bootstrap instanceof RoutesInterface) {
				$bootstrap->registerRoutes($this->_services['routes']);
			}
		}

		// Register fallback routes
		foreach ($this->_bootstraps as $bootstrap) {
			if ($bootstrap instanceof FallbackRoutesInterface) {
				$bootstrap->registerFallbackRoutes($this->_services['routes']);
			}
		}

		// Register events and tasks last
		foreach ($this->_bootstraps as $bootstrap) {
			if ($bootstrap instanceof EventsInterface) {
				$bootstrap->registerEvents($this->_services['event.dispatcher']);
			}
			if ('console' === $this->_services['environment']->context()) {
				if ($bootstrap instanceof CommandsInterface) {
					$bootstrap->registerCommands($this->_services['console.commands']);
				}
				if ($bootstrap instanceof TasksInterface) {
					$bootstrap->registerTasks($this->_services['task.collection']);
				}
			}
		}

		// Clear the bootstrap list
		$this->clear();
	}

	public function loadServices()
	{

	}

	public function loadRoutes()
	{

	}

	public function loadFallbackRoutes()
	{

	}

	public function loadTasks()
	{

	}


	/**
	 * Get all bootstraps registered on this loader.
	 *
	 * @return array Array of bootstraps set on this loader
	 */
	public function getBootstraps()
	{
		return $this->_bootstraps;
	}

	/**
	 * Enable bootstrap class path caching.
	 *
	 * @return void
	 */
	public function enableCaching()
	{
		$this->_cacheEnabled = true;
	}

	/**
	 * Disable bootstrap class path caching.
	 *
	 * @return void
	 */
	public function disableCaching()
	{
		$this->_cacheEnabled = false;
	}

	/**
	 * Clear all bootstraps from this loader.
	 *
	 * @return Loader Returns $this for chaining
	 */
	public function clear()
	{
		$this->_bootstraps = array();

		return $this;
	}
}