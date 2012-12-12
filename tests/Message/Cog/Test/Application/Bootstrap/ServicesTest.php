<?php

namespace Message\Cog\Test\Application\Bootstrap;

use Message\Cog\Application\Bootstrap\Services as ServicesBootstrap;

use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Service\SharedServiceIdentifier;
use Message\Cog\Test\Application\FauxLoader as AppFauxLoader;
use Message\Cog\Test\Bootstrap\FauxLoader as BootstrapFauxLoader;

use Closure;

class ServicesTest extends \PHPUnit_Framework_TestCase
{
	protected $_container;
	protected $_bootstrap;

	public function setUp()
	{
		// Set the command-line arguments (override the phpunit ones)
		$_SERVER['argv'] = array('/usr/bin/php', 'foo:bar1');

		$this->_container = new FauxContainer;
		$this->_bootstrap = new ServicesBootstrap;

		// Define services normally defined in Application\Loader.
		$this->_container['app.loader'] = function($c) {
			return new AppFauxLoader(dirname(__FILE__));
		};

		$this->_container['bootstrap.loader'] = function($c) {
			return new BootstrapFauxLoader($c);
		};

		$this->_bootstrap->registerServices($this->_container);
	}

	public function testProfilerDefinition()
	{
		$this->assertTrue($this->_container->isShared('profiler'));
		$this->assertInstanceOf('Message\Cog\Debug\Profiler', $this->_container['profiler']);
	}

	public function testClassLoaderDefinition()
	{
		$this->assertInstanceOf('Composer\Autoload\ClassLoader', $this->_container['class.loader']);
	}

	public function testEnvironmentDefinitions()
	{
		$this->assertInstanceOf('Message\Cog\Environment', $this->_container['environment']);
		$this->assertInternalType('string', $this->_container['env']);
	}

	public function testEventsDefinitions()
	{
		$this->assertInstanceOf('Message\Cog\Event\Event', $this->_container['event']);

		$this->assertTrue($this->_container->isShared('event.dispatcher'));
		$this->assertInstanceOf(
			'Message\Cog\Event\DispatcherInterface',
			$this->_container['event.dispatcher']
		);
	}

	public function testRouterDefinition()
	{
		$this->assertTrue($this->_container->isShared('router'));
		$this->assertInstanceOf('Message\Cog\Routing\RouterInterface', $this->_container['router']);
	}

	public function testControllerResolverDefinition()
	{
		$this->assertTrue($this->_container->isShared('controller.resolver'));
		$this->assertInstanceOf(
			'Message\Cog\Controller\ControllerResolverInterface',
			$this->_container['controller.resolver']
		);
	}

	public function testTemplatingDefinition()
	{
		$this->assertTrue($this->_container->isShared('templating'));
		$this->assertInstanceOf(
			'Message\Cog\Templating\EngineInterface',
			$this->_container['templating']
		);
	}

	public function testHTTPDispatcherDefinition()
	{
		$this->assertInstanceOf('Message\Cog\HTTP\Dispatcher', $this->_container['http.dispatcher']);
	}

	public function testResponseBuilderDefinition()
	{
		$this->assertTrue($this->_container->isShared('response_builder'));
		$this->assertInstanceOf(
			'Message\Cog\Controller\ResponseBuilder',
			$this->_container['response_builder']
		);
	}

	public function testConfigDefinition()
	{
		$this->assertTrue($this->_container->isShared('config'));
		$this->assertInstanceOf(
			'Message\Cog\Config',
			$this->_container['config']
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
			'Message\Cog\Console\TaskCollection',
			$this->_container['task.collection']
		);
	}
	public function testReferenceParserDefinitions()
	{
		$this->assertInstanceOf(
			'Message\Cog\ReferenceParserInterface',
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