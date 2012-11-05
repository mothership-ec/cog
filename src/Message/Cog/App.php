<?php

namespace Message\Cog;

class App
{
	protected $_services;

	public function __construct()
	{
		$this->_setupConstants();

		// Set the default timezone
		date_default_timezone_set('Europe/London');

		$this->setupAutoloader();
	}

	/**
	 * A drop in replacement for old calls to include 'global/loader.php'.
	 *
	 * TODO: Remove this method when no other files call it.
	 *
	 * @return void
	 */
	public static function loader()
	{
		$app = new self;
		$app->setupFrameworkServices();
		$app->setupWebServices();
	}

	/**
	 * Sets up and configures the autoloader and some key services.
	 *
	 * @return void
	 */
	public function setupAutoloader()
	{
		// Include composer autoloader
		require_once ROOT_PATH . 'vendor/autoload.php';

		// Setup the environment
		// TODO: move as much of this as we can in to a Bootstrap
		$this->_services = Services::instance();

		// Register framework services
		$serviceBootstrap = new \Message\Cog\Bootstrap\Services;
		$serviceBootstrap->registerServices($this->_services);

		$this->_services['class.loader'] = function() {
			return \ComposerAutoloaderInit::getLoader();
		};

		$app = $this;
		$this->_services['app'] = function() use ($app) {
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
		if ($this->_services['environment']->isLocal()) {
			$services = $this->_services;
			// TODO: make this get the query count from new query objects
			$profiler = new \Message\Cog\Profiler(null, null, false);

			register_shutdown_function(function() use ($profiler, $services) {
				if($services['environment']->isLocal() && $services['environment']->context() != 'console' && (!isset($GLOBALS['page']) || !in_array($GLOBALS['page']->getTemplateName(), array('Blank', 'Ajax', 'Xml', 'Print', 'AdminPrint')))) {
					echo $profiler->renderHtml();
				}
			});
		}

		// Load modules
		$this->_services['module.loader']->run(
			$this->_services['config']->modules,
			$this->_services['class.loader']->getNamespaces()
		);
	}

	/**
	 * Setup some services, singletons and globals that only apply to
	 * web requests.
	 *
	 * @return void
	 */
	public function setupWebServices()
	{
		startSession();

		// Set up SOAP ini options
		ini_set('soap.wsdl_cache_enabled', 0);
	}

	/**
	 * Initiates a web request.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->setupFrameworkServices();
		$this->setupWebServices();

		$GLOBALS['t'] = new \Timer;

		$this->_services['http.request.master'] = $this->_services->share(function() {
			return HTTP\Request::createFromGlobals();
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
		// TODO: No global constants, make them part of Mothership\App
		define('ROOT_PATH', realpath(__DIR__ . '/../../../').'/');
		define('SYSTEM_PATH', ROOT_PATH.'system/');
		define('AREA', preg_replace('/^(.*)\//', '', $_SERVER['DOCUMENT_ROOT']));
		define('PUBLIC_PATH', SYSTEM_PATH.'public/'.AREA.'/');
	}
}