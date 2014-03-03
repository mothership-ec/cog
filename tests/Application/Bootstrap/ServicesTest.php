<?php

namespace Message\Cog\Test\Application\Bootstrap;

use Message\Cog\Application\Bootstrap\Services as ServicesBootstrap;

use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Service\SharedServiceIdentifier;
use Message\Cog\Test\Application\FauxEnvironment;
use Message\Cog\Test\Application\FauxLoader as AppFauxLoader;
use Message\Cog\Test\Bootstrap\FauxLoader as BootstrapFauxLoader;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

use Closure;

class ServicesTest extends \PHPUnit_Framework_TestCase
{
	protected $_container;
	protected $_bootstrap;

	/**
	 * Set up everything that is assumed for this bootstrap to run.
	 *
	 * This is mostly services that are used by other services that are defined
	 * earlier.
	 *
	 * @todo This is a god awful mess. Find a way to improve it.
	 */
	public function setUp()
	{
		// Set the command-line arguments (override the phpunit ones)
		$_SERVER['argv'] = array('/usr/bin/php', 'foo:bar1');

		$this->_container = new FauxContainer;
		$this->_bootstrap = new ServicesBootstrap;

		$requestContext  = $this->getMock('Message\\Cog\\Routing\\RequestContext');
		$routeCollection = new \Symfony\Component\Routing\RouteCollection;

		// Add config directory as the config loader needs it
		vfsStream::setup('root');
		vfsStream::newDirectory('config')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newDirectory('translations')
			->at(vfsStreamWrapper::getRoot());

		vfsStream::newFile('db.yml', 0777)
			->at(VfsStreamWrapper::getRoot()->getChild('config'))
			->setContent('hostname: 127.0.0.1
user: user
pass: password
name: table_name
charset: utf8');

		vfsStream::newFile('app.yml', 0777)
			->at(VfsStreamWrapper::getRoot()->getChild('config'))
			->setContent('name: My Application
base-url: default.com
default-contact-email: dev@message.co.uk
default-email-from:
  email: noreply@default.com
  name: My Application
csrf-secret: CHANGE THIS FOR EACH INSTALLATION
session-namespace: cog');

		// Define services normally defined in Application\Loader
	//	$classLoaderMock = $this->getMock('Composer\\Autoload\\ClassLoader');
		$this->_container['class.loader'] = function($c) {
			return require getcwd() . '/vendor/autoload.php';
		};

		$this->_container['app.loader'] = function($c) {
			$loader = new AppFauxLoader($c['class.loader'], vfsStream::url('root'));
			$loader->setServiceContainer($c);

			return $loader;
		};

		$env = new FauxEnvironment;
		$env->set('test');
		$this->_container['environment'] = $this->_container->share(function() use ($env) {
			return $env;
		});
		$this->_container['env'] = function($c) {
			return $c['environment']->get();
		};

		$cacheAdapter = $this->getMockBuilder('Message\\Cog\\Cache\\Adapter\\Filesystem')
			->disableOriginalConstructor()
			->getMock();

		$cache = $this->getMockBuilder('Message\\Cog\\Cache\\Instance')
			->disableOriginalConstructor()
			->getMock();

		$this->_container['cache.adapter'] = $this->_container->share(function() use ($cacheAdapter) {
			return $cacheAdapter;
		});

		$this->_container['cache'] = $this->_container->share(function($c) use ($cache) {
			$cache->setPrefix(implode('.', array(
				$c['app.loader']->getAppName(),
				$c['environment']->get(),
				$c['environment']->installation(),
			)));

			return $cache;
		});


		$this->_container['bootstrap.loader'] = function($c) {
			return new BootstrapFauxLoader($c);
		};

		$this->_container['routes.compiled'] = function() use ($routeCollection) {
			return $routeCollection;
		};

		$this->_container['http.request.context'] = function() use ($requestContext) {
			return $requestContext;
		};

		$this->_bootstrap->registerServices($this->_container);
	}

	public function testProfilerDefinition()
	{
		$this->assertTrue($this->_container->isShared('profiler'));
		$this->assertInstanceOf('Message\Cog\Debug\Profiler', $this->_container['profiler']);
	}

	public function testEventsDefinitions()
	{
		$this->assertFalse($this->_container->isShared('event'));
		$this->assertInstanceOf('Message\Cog\Event\Event', $this->_container['event']);

		$this->assertTrue($this->_container->isShared('event.dispatcher'));
		$this->assertInstanceOf(
			'Message\Cog\Event\DispatcherInterface',
			$this->_container['event.dispatcher']
		);
	}

	public function testRoutingDefinitions()
	{
		$this->assertTrue($this->_container->isShared('routes'));
		$this->assertInstanceOf('Message\Cog\Routing\CollectionManager', $this->_container['routes']);

		$this->assertFalse($this->_container->isShared('routing.matcher'));
		$this->assertInstanceOf('Message\Cog\Routing\UrlMatcher', $this->_container['routing.matcher']);
		$this->assertFalse($this->_container->isShared('routing.generator'));
		$this->assertInstanceOf('Message\Cog\Routing\UrlGenerator', $this->_container['routing.generator']);
	}

	public function testTemplatingDefinition()
	{
		$this->assertFalse($this->_container->isShared('templating'));
		$this->assertInstanceOf(
			'Message\Cog\Templating\EngineInterface',
			$this->_container['templating']
		);
	}

	public function testResponseBuilderDefinition()
	{
		$this->assertTrue($this->_container->isShared('response_builder'));
		$this->assertInstanceOf(
			'Message\Cog\Controller\ResponseBuilder',
			$this->_container['response_builder']
		);
	}

	public function testConfigDefinitions()
	{
		$this->assertTrue($this->_container->isShared('cfg'));
		$this->assertInstanceOf(
			'Message\Cog\Config\Registry',
			$this->_container['cfg']
		);

		$this->assertTrue($this->_container->isShared('config.loader'));
		$this->assertInstanceOf(
			'Message\Cog\Config\LoaderInterface',
			$this->_container['config.loader']
		);
	}

	public function testModuleLoaderAndLocatorDefinitions()
	{
		$this->assertTrue($this->_container->isShared('module.locator'));
		$this->assertInstanceOf(
			'Message\Cog\Module\LocatorInterface',
			$this->_container['module.locator']
		);

		$this->assertTrue($this->_container->isShared('module.loader'));
		$this->assertInstanceOf(
			'Message\Cog\Module\Loader',
			$this->_container['module.loader']
		);
	}

	public function testTaskCollectionDefinitions()
	{
		$this->assertTrue($this->_container->isShared('task.collection'));
		$this->assertInstanceOf(
			'Message\\Cog\\Console\\Task\\Collection',
			$this->_container['task.collection']
		);
	}
	public function testReferenceParserDefinitions()
	{
		$this->assertTrue($this->_container->isShared('reference_parser'));
		$this->assertInstanceOf(
			'Message\Cog\Module\ReferenceParserInterface',
			$this->_container['reference_parser']
		);
	}

	public function testApplicationContextDefinitions()
	{
		$this->assertTrue($this->_container->isShared('app.context.web'));
		$this->assertInstanceOf(
			'Message\Cog\Application\Context\Web',
			$this->_container['app.context.web']
		);

		$this->assertTrue($this->_container->isShared('app.context.console'));
		$this->assertInstanceOf(
			'Message\Cog\Application\Context\Console',
			$this->_container['app.context.console']
		);
	}

	public function testFunctionClassDefinitions()
	{
		$functionClasses = array(
			'text',
			'utility',
			'debug',
		);

		foreach ($functionClasses as $functionClass) {
			$serviceName  = 'fns.' . $functionClass;

			$this->assertTrue($this->_container->isShared($serviceName));
			$this->assertInstanceOf(
				'Message\Cog\Functions\\' . ucfirst($functionClass),
				$this->_container[$serviceName]
			);
		}
	}
}