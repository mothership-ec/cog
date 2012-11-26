<?php

namespace Message\Cog\Application;

abstract class Loader
{
	protected $_baseDir;
	protected $_services;

	public function __construct($baseDir)
	{
		// Ensure base directory ends with directory separator
		if (substr($baseDir, -1) !== DIRECTORY_SEPARATOR) {
			$baseDir .= DIRECTORY_SEPARATOR;
		}

		$this->_baseDir = $baseDir;

		$this->_setupConstants();

		// Set the default timezone
		date_default_timezone_set('Europe/London');

		$this->setupAutoloader();
	}

	/**
	 * Sets up and configures the autoloader and some key services.
	 *
	 * @return void
	 */
	public function setupAutoloader()
	{
		// Include composer autoloader
		require_once $this->_baseDir . 'vendor/autoload.php';

		// Setup the environment
		// TODO: move as much of this as we can in to a Bootstrap
		$this->_services = \Message\Cog\Service\Container::instance();

		// Register framework services
		$serviceBootstrap = new \Message\Cog\Bootstrap\Services;
		$serviceBootstrap->registerServices($this->_services);

		$app = $this;
		$this->_services['app.loader'] = function() use ($app) {
			return $app;
		};

		// Register framework events
		$eventBootstrap = new \Message\Cog\Bootstrap\Events;
		$eventBootstrap->registerEvents($this->_services['event.dispatcher']);
	}

	/**
	 * Sets up some generic services needed by the Framework and all requests.
	 *
	 * @return void
	 */
	public function setupFrameworkServices()
	{
		// Load modules
		$this->_services['module.loader']->run($this->_registerModules());
	}

	/**
	 * Initiates a web request.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->setupFrameworkServices();

		$this->_services['http.request.master'] = $this->_services->share(function() {
			return \Message\Cog\HTTP\Request::createFromGlobals();
		});

		$this->_services['http.dispatcher']
			->handle($this->_services['http.request.master'])
			->send();
	}

	/**
	 * Defines all global constants used throughout the app.
	 *
	 * @return void
	 */
	protected function _setupConstants()
	{
		// TODO: No global constants, make them part of Cog\App
		define('ROOT_PATH', $this->_baseDir);
		define('SYSTEM_PATH', ROOT_PATH.'system/');
		define('AREA', preg_replace('/^(.*)\//', '', $_SERVER['DOCUMENT_ROOT']));
		define('PUBLIC_PATH', SYSTEM_PATH.'public/'.AREA.'/');
	}

	abstract protected function _registerModules();
}