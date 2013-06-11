<?php

namespace Message\Cog\Application;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\Container as ServiceContainer;

use RuntimeException;
use LogicException;

use Composer\Autoload\ClassLoader;

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
	 * Sets the application base directory, ensuring it ends with a trailing
	 * slash.
	 *
	 * @param string $baseDir Absolute path to the installation base directory
	 */
	public function __construct($baseDir, ClassLoader $autoloader)
	{
		$this->_baseDir    = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->_autoloader = $autoloader;
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
	 * Get the application name.
	 *
	 * This gets the first namespace for the application's loader (the subclass
	 * that extends this abstract class).
	 *
	 * E.g. a loader named TestApp\MySetupModule\AppLoader would return `TestApp`
	 *
	 * @return string The application name
	 */
	public function getAppName()
	{
		return strstr(get_class($this), '\\', true);
	}

	/**
	 * Get the context instance for this request.
	 *
	 * @return Context\ContextInterface The context instance
	 * @throws LogicException           If the context has not been set yet
	 */
	public function getContext()
	{
		if (!$this->_context) {
			throw new LogicException('Cannot get context: it has not yet been set. Please run `loadCog()` first.');
		}

		return $this->_context;
	}

	/**
	 * Shortcut method for initialising, loading & executing the application.
	 *
	 * @see initialise
	 * @see loadCog
	 * @see setContext
	 * @see loadModules
	 * @see execute
	 *
	 * @return mixed The result of running `$this->execute()`
	 */
	public function run()
	{
		return $this
			->initialise()
			->loadCog()
			->setContext()
			->loadModules()
			->execute();
	}

	/**
	 * Set the service container to use.
	 *
	 * This gets set automatically, so this method is only for overriding the
	 * container. Handy for unit testing.
	 *
	 * @param  ContainerInterface $container The service container to use
	 * @return Loader                        Returns $this for chainability
	 */
	public function setServiceContainer(ContainerInterface $container)
	{
		$this->_services = $container;

		return $this;
	}

	/**
	 * Initialises the application & sets up a service definition for the
	 * Composer autoloader class.
	 *
	 * To find the instance of the Composer autoloader class without having to
	 * instantiate a new one, we have to search through all declared classes and
	 * find one that starts with 'ComposerAutoloaderInit' and define it as the
	 * `class.loader` service.
	 *
	 * @see _setDefaults
	 * @see _includeAutoloader
	 *
	 * @return Loader Returns $this for chainability
	 */
	public function initialise()
	{
		$this->_setDefaults();

		// Create the service container if not already created
		if (!isset($this->_services)) {
			$this->setServiceContainer(ServiceContainer::instance());
		}

		$autoloader = $this->_autoloader;
		// Set up service definition for Composer autoloader class
		$this->_services['class.loader'] = $this->_services->share(function() use ($autoloader) {
			return $autoloader;
		});

		return $this;
	}

	/**
	 * Instantiates the service container, runs the Cog bootstraps and sets the
	 * context.
	 *
	 * @return Loader Returns $this for chainability
	 */
	public function loadCog()
	{
		// Add the application loader as a service
		$appLoader = $this;
		$this->_services['app.loader'] = $this->_services->share(function() use ($appLoader) {
			return $appLoader;
		});

		// Register the service for the bootstrap loader
		$this->_services['bootstrap.loader'] = function($c) {
			return new \Message\Cog\Bootstrap\Loader($c);
		};

		// Load the Cog bootstraps
		$this->_services['bootstrap.loader']->addFromDirectory(
			__DIR__ . '/Bootstrap',
			'Message\Cog\Application\Bootstrap'
		)->load();

		$this->_services['event.dispatcher']->dispatch(
			'cog.load.success',
			$this->_services['event']
		);

		return $this;
	}

	/**
	 * Set the context class for this request.
	 *
	 * This looks at the context set on the `Environment` class and looks for
	 * a definition on the service container named `app.context.[$contextName]`.
	 *
	 * @return Loader            Returns $this for chainability
	 *
	 * @throws \RuntimeException If the apropriate context class could not be
	 *                           found on the service container.
	 * @throws \LogicException   If the context class was found, but it does not
	 *                           implement `ContextInterface`.
	 */
	public function setContext()
	{
		$contextName = $this->_services['environment']->context();
		$serviceName = 'app.context.' . $contextName;

		if (!isset($this->_services[$serviceName])) {
			throw new RuntimeException(
				sprintf('Context class not defined on service container as `%s`.', $serviceName)
			);
		}

		if (!in_array('Message\Cog\Application\Context\ContextInterface', class_implements($this->_services[$serviceName]))) {
			throw new LogicException(
				sprintf('Context class service definition does not implement ContextInterface: `%s`', $serviceName)
			);
		}

		$this->_context = $this->_services[$serviceName];

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
		$return = $this->getContext()->run();

		$this->_services['event.dispatcher']->dispatch(
			'terminate',
			$this->_services['event']
		);

		return $return;
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