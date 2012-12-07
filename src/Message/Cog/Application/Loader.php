<?php

namespace Message\Cog\Application;

use Message\Cog\Service\ContainerAwareInterface;

/**
 * Cog application loader.
 *
 * Responsible for instantiating the autoloader and loading bootstraps for Cog
 * and all modules defined in the abstract `_registerModules()` method.
 *
 * In an installation, a class should be created that extends this class and an
 * array of module names to load should be returned in `_registerModules()`.
 * The class can be named anything and placed anywhere, but normally this is:
 * `/app/[AppName]/App.php`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author James Moss <james@message.co.uk>
 */
abstract class Loader
{
	protected $_context;
	protected $_baseDir;
	protected $_services;

	/**
	 * Constructor.
	 *
	 * Sets the application base directory.
	 *
	 * @param string $baseDir Absolute path to the installation base directory
	 */
	public function __construct($baseDir)
	{
		// Ensure base directory ends with directory separator
		if (DIRECTORY_SEPARATOR !== substr($baseDir, -1)) {
			$baseDir .= DIRECTORY_SEPARATOR;
		}

		$this->_baseDir = $baseDir;
	}

	/**
	 * Get the base directory of the application.
	 *
	 * @return string The application's base directory
	 */
	public function getBaseDir()
	{
		return $this->_baseDir;
	}

	/**
	 * Get the context instance for this request.
	 *
	 * @return Context\ContextInterface The context instance
	 * @throws \Exception               If the context has not been set yet
	 */
	public function getContext()
	{
		if (!$this->_context) {
			throw new \Exception('Cannot get context: it has not yet been set. Please run `loadCog()` first.');
		}

		return $this->_context;
	}

	/**
	 * Shortcut method for initialising, loading & executing the application.
	 *
	 * @see initialise
	 * @see loadCog
	 * @see loadModules
	 * @see execute
	 *
	 * @return mixed Whatever is returned by `$this->execute()`
	 */
	public function run()
	{
		return $this->initialise()->loadCog()->loadModules()->execute();
	}

	/**
	 * Initialises the application.
	 *
	 * @see _setDefaults
	 * @see _includeAutoloader
	 *
	 * @return Loader Returns $this for chainability
	 */
	public function initialise()
	{
		$this->_setDefaults();
		$this->_includeAutoloader();

		return $this;
	}

	/**
	 * Instantiates the service container, runs the Cog bootstraps and sets the
	 * context.
	 *
	 * @see _setContext
	 *
	 * @return Loader Returns $this for chainability
	 */
	final public function loadCog()
	{
		// Create the service container
		$this->_services = \Message\Cog\Service\Container::instance();

		// Add the application loader as a service
		$app = $this;
		$this->_services['app.loader'] = function() use ($app) {
			return $app;
		};

		// Register the service for the bootstrap loader
		$this->_services['bootstrap.loader'] = function($c) {
			return new \Message\Cog\Bootstrap\Loader($c);
		};

		// Load the Cog bootstraps
		$this->_services['bootstrap.loader']->addFromDirectory(
			__DIR__ . '/Bootstrap',
			'Message\Cog\Application\Bootstrap'
		)->load();

		// Set the context
		$this->_setContext();

		return $this;
	}

	/**
	 * Loads the modules defined by `_registerModules()`.
	 *
	 * @return Loader Returns $this for chainability
	 */
	public function loadModules()
	{
		$this->_services['module.loader']->run($this->_registerModules());

		return $this;
	}

	/**
	 * Executes the application. This invokes `run()` on the context class.
	 *
	 * @see Message\Cog\Application\Context\ContextInterface::run()
	 *
	 * @return mixed Whatever is returned by `run()` on the context class
	 */
	public function execute()
	{
		return $this->_context->run();
	}

	/**
	 * Include the Composer autoloader.
	 *
	 * This is determined by looking for the `vendor` directory within the
	 * defined base directory, and the file `autoload.php` within that.
	 *
	 * @throws \Exception If the autoloader file can't be found
	 * @throws \Exception If the autoloader file is not readable
	 */
	protected function _includeAutoloader()
	{
		$autoloadPath = $this->_baseDir . 'vendor/autoload.php';

		if (!file_exists($autoloadPath)) {
			throw new \Exception(sprintf('Autoloader file at `%s` could not be found. Is the base directory set correctly?', $autoloadPath));
		}

		if (!is_readable($autoloadPath)) {
			throw new \Exception(sprintf('Autoloader file at `%s` is not readable.', $autoloadPath));
		}

		require_once $this->_baseDir . 'vendor/autoload.php';
	}

	/**
	 * Set the context class for this request.
	 *
	 * This looks at the context set on the `Environment` class and looks for
	 * a context class matching that name in the `Message\Cog\Application\Context`
	 * namespace.
	 *
	 * @throws \RuntimeException If the apropriate context class could not be found
	 * @throws \LogicException   If the context class was found, but it does not
	 *                           implement `ContextInterface`.
	 */
	final protected function _setContext()
	{
		$context   = $this->_services['environment']->context();
		$className = 'Message\\Cog\\Application\\Context\\' . ucfirst($context);

		if (!class_exists($className)) {
			throw new \RuntimeException(
				sprintf('Context class not found for context: `%s`', $context)
			);
		}

		if (!in_array('Message\Cog\Application\Context\ContextInterface', class_implements($className))) {
			throw new \LogicException(
				sprintf('Context class does not implement ContextInterface: `%s`', $context)
			);
		}

		return $this->_context = new $className($this->_services);
	}

	/**
	 * Apply some default PHP settings for the application.
	 *
	 * Currently this only covers the default timezone to avoid avoid a strict
	 * standards error.
	 */
	protected function _setDefaults()
	{
		// Set the default timezone
		date_default_timezone_set('Europe/London');
	}

	/**
	 * Returns an array of modules to load. Defined by installation application
	 * subclass.
	 *
	 * Modules are loaded in the order they are defined here.
	 *
	 * @return array List of modules to load
	 */
	abstract protected function _registerModules();
}