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
		// Include Composer autoloader
		require_once ROOT_PATH . 'vendor/autoload.php';

		$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader;
		$loader->register();
		$loader->registerNamespaces(array(
			// Core system
			'Cog'   => CLASS_AUTOLOAD_PATH,
		));

		// Deprecated autoloader
		// TODO: Remove once everything is PSR-0
		require_once SYSTEM_PATH.'library/Mothership/Framework/Functions/autoload.php';

		// Add pear to include path
		// This is to handle the require_once calls in the Image_Barcode2 class
		// TODO: try and get rid of this one day...
		ini_set('include_path', '.:' . ROOT_PATH . 'vendor/pear/Image_Barcode2:'.ini_get('include_path'));

		// Setup the environment
		// TODO: move as much of this as we can in to a Bootstrap
		$this->_services = Services::instance();

		// Register framework services
		$serviceBootstrap = new \Mothership\Framework\Bootstrap\Services;
		$serviceBootstrap->registerServices($this->_services);

		$this->_services['class.loader'] = function() use ($loader) {
			return $loader;
		};

		$app = $this;
		$this->_services['app'] = function() use ($app) {
			return $app;
		};

		// Register framework events
		$eventBootstrap = new \Mothership\Framework\Bootstrap\Events;
		$eventBootstrap->registerEvents($this->_services['event.dispatcher']);
	}

	/**
	 * Sets up some generic services needed by the Framework and all requests.
	 *
	 * @return void
	 */
	public function setupFrameworkServices()
	{
		// Old style config files
		// TODO: Remove these few lines
		require ROOT_PATH . 'config/deprecated/Order.cfg.php';
		require ROOT_PATH . 'config/deprecated/stockLocations.cfg.php';

		// Load functions
		// TODO: Reduce the amount of functions or combine multiple into a single file
		// loading all those files adds about 30-50ms to the startup time of a page
		// TODO: skip classes here somehow, as there is one or two function classes
		$files = glob(SYSTEM_PATH . 'library/Mothership/Framework/Functions/*.php');
		foreach ($files as $file) {
			require_once $file;
		}

		// Set up test services
		// TODO: we need some functionality to run a services bootstrap for running in unit test mode

		if ($this->_services['environment']->isLocal()) {
			$services = $this->_services;
			$profiler = new \Mothership\Framework\Profiler(null, function(){
				return \DBconnect::instance()->getQueryCount();
			}, false);

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

		// Configure database connection
		// TODO: Make this lazy connecting. At the moment it adds ~50ms to startup time.
		#$DBC = \DBconnect::getInstance();
		#$DBC->configure($this->_services['config']->db->host, $this->_services['config']->db->user, $this->_services['config']->db->password, $this->_services['config']->db->database);
		#$DBC->setCharset($this->_services['config']->db->charset);
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

		// Grab an instance of feedback
		$FB = \Feedback::instance();

		// Load deprecated config
		$this->setupDeprecated();

		// Page load listeners
		// TODO: Move these to an event and add listener in Mothership\Core

		// Empty a basket
		if(isset($_GET['empty'])) {
			// Grab a new id
			session_regenerate_id();
			unset(
				$_SESSION['basket'],
				$_SESSION['campaign'],
				$_SESSION['CHECKOUT']
			);
			\PersistentBasket::instance()->destroy();
		}

		// Create a new basket if we don't have one
		if(!isset($_SESSION['basket'])) {
			$_SESSION['basket'] = new \Basket();
			\PersistentBasket::instance()->load();
		}

		// Reload basket to ensure locale changes get reflected
		#$locale = \Locale::instance();
		#$_SESSION['basket']->reload($locale->getId());

		// Check for user cookie, set session if valid
		recoverLogin();
	}

	/**
	 * Some deprecated globals and DB connections
	 *
	 * TODO: Remove this method when legacy code has been removed.
	 *
	 * @return void
	 */
	public function setupDeprecated()
	{
		$services = Services::instance();

		// Don't call this method if we're not in a legacy page
		if($_SERVER['PHP_SELF'] == '/app.php' || $services['env'] == 'test') {
			return;
		}

		#mysql_connect($services['config']->db->host, $services['config']->db->user, $services['config']->db->password);
		#mysql_query("SET NAMES ".$services['config']->db->charset);
		#mysql_select_db($services['config']->db->database);

		$GLOBALS['root_dir'] = SYSTEM_PATH;
		$GLOBALS['site_dir'] = "public_html/";
		$GLOBALS['site']     = 'http://' . $_SERVER['HTTP_HOST'];
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
		define('ROOT_PATH', realpath(__DIR__ . '/../../../../').'/');
		define('TMP_PATH', ROOT_PATH.'tmp/');
		define('LOG_PATH', ROOT_PATH.'log/');
		define('SYSTEM_PATH', ROOT_PATH.'system/');
		define('CLASS_AUTOLOAD_PATH', SYSTEM_PATH . 'library');
		define('AREA', preg_replace('/^(.*)\//', '', $_SERVER['DOCUMENT_ROOT']));
		define('PUBLIC_PATH', SYSTEM_PATH.'public/'.AREA.'/');
	}
}