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
	protected $_baseDir;
	protected $_services;

	/**
	 * Constructor.
	 *
	 * @param string $baseDir Absolute path to the installation base directory
	 */
	final public function __construct($baseDir)
	{
		// Ensure base directory ends with directory separator
		if (substr($baseDir, -1) !== DIRECTORY_SEPARATOR) {
			$baseDir .= DIRECTORY_SEPARATOR;
		}

		$this->_baseDir = $baseDir;

		// Ensure the composer autoloader has been included
		require_once $this->_baseDir . 'vendor/autoload.php';

		$this->_setDefaults();
	}

	/**
	 * Get the base directory of the application.
	 *
	 * @return string The application's base directory
	 */
	final public function getBaseDir()
	{
		return $this->_baseDir;
	}

	final public function init()
	{
		$this->_loadCog();
		$this->_loadModules();

		return $this;
	}

	final public function getContext()
	{
		$context   = $this->_services['environment']->context();
		$className = 'Message\\Cog\\Application\\Context\\' . ucfirst($context);

		if (!class_exists($className)) {
			// TODO: Change to Application\Exception\UnknownContextException
			throw new \RuntimeException(
				sprintf('Context class not found for context: `%s`', $context)
			);
		}

		$context = new $className;

		if ($context instanceof ContainerAwareInterface) {
			$context->setContainer($this->_services);
		}

		return $context;
	}

	/**
	 * Loads the application & runs the appropriate context.
	 *
	 * @return Whatever is returned by `run()` on the context
	 */
	final public function run()
	{
		return $this->init()->getContext()->run();
	}

	/**
	 * Apply some default PHP settings for the application.
	 *
	 * Currently this only covers the default timezone to avoid avoid a strict
	 * standards error.
	 *
	 * @return void
	 */
	final protected function _setDefaults()
	{
		// Set the default timezone
		date_default_timezone_set('Europe/London');
	}

	/**
	 * Instantiate the autoloader and run Cog bootstraps
	 *
	 * @return void
	 */
	final protected function _loadCog()
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
	}

	/**
	 * Loads the modules defined by `_registerModules()`.
	 *
	 * @return void
	 */
	final protected function _loadModules()
	{
		$this->_services['module.loader']->run($this->_registerModules());
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