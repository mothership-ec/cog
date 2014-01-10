<?php

namespace Message\Cog\Test\Bootstrap;

use Message\Cog\Bootstrap\Loader;
use Message\Cog\HTTP\Request;

use Message\Cog\Test\Application\FauxEnvironment;
use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Event\FauxDispatcher;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	protected $_services;
	protected $_loader;

	static public function getContexts()
	{
		return array(
			array('console'),
			array('web'),
		);
	}

	public function setUp()
	{
		$this->_services = new FauxContainer;
		$this->_loader   = new Loader($this->_services);
	}

	public function testChainability()
	{
		$this->assertEquals($this->_loader, $this->_loader->add(new Mocks\FauxFullBootstrap));
		$this->assertEquals($this->_loader, $this->_loader->addFromDirectory(dirname(__FILE__), 'Not\A\Namespace'));
		$this->assertEquals($this->_loader, $this->_loader->clear());
	}

	public function testAdd()
	{
		$bootstrap = new Mocks\FauxFullBootstrap;

		$this->assertInternalType('array', $this->_loader->getBootstraps());
		$this->assertEmpty($this->_loader->getBootstraps());

		$this->_loader->add($bootstrap);

		$this->assertEquals(array($bootstrap), $this->_loader->getBootstraps());

		return $this->_loader;
	}

	/**
	 * @depends testAdd
	 */
	public function testClear($loader)
	{
		$loader->clear();

		$this->assertInternalType('array', $loader->getBootstraps());
		$this->assertEmpty($loader->getBootstraps());
	}

	/**
	 * @todo Refactor so this test does not assume the directory read ordering
	 *       will be by filename ascending - this might be different on different
	 *       operating systems or distros.
	 */
	public function testAddFromDirectory()
	{
		$this->_services['request'] = $this->_services->share(function() {
			return new Request;
		});

		$this->_loader->addFromDirectory(dirname(__FILE__) . '/Mocks', 'Message\Cog\Test\Bootstrap\Mocks');

		$bootstraps = $this->_loader->getBootstraps();

		// Assert only the valid bootstraps were loaded
		$this->assertCount(4, $bootstraps);
		$this->assertInstanceOf('Message\Cog\Test\Bootstrap\Mocks\ContainerAwareBootstrap', $bootstraps[0]);
		$this->assertInstanceOf('Message\Cog\Test\Bootstrap\Mocks\FauxFullBootstrap', $bootstraps[1]);
		$this->assertInstanceOf('Message\Cog\Test\Bootstrap\Mocks\MethodCallOrderTesterBootstrap', $bootstraps[2]);
		$this->assertInstanceOf('Message\Cog\Test\Bootstrap\Mocks\RequestAwareBootstrap', $bootstraps[3]);

		// Assert the 'Aware' bootstraps were made aware!
		$this->assertEquals($this->_services, $bootstraps[0]->getContainer());
		$this->assertEquals($this->_services['request'], $bootstraps[3]->getRequest());
	}

	/**
	 * @dataProvider getContexts
	 */
	public function testLoad($context)
	{
		$routesMock                 = $this->getMock('Message\Cog\Router\CollectionManager');
		$methodCallLoggingBootstrap = new Mocks\MethodCallOrderTesterBootstrap;
		$mockBootstrap              = $this->getMock('Message\Cog\Test\Bootstrap\Mocks\FauxFullBootstrap');
		$registerMethodOrder        = array(
			'registerServices',
			'registerRoutes',
			'registerEvents',
		);

		$this->_services['routes'] = function() use ($routesMock) {
			return $routesMock;
		};

		$this->_services['event.dispatcher'] = function() {
			return new FauxDispatcher;
		};

		$this->_services['task.collection'] = function() {
			return array();
		};

		$this->_services['environment'] = $this->_services->share(function() use ($context) {
			$env = new FauxEnvironment;
			$env->setContext($context);

			return $env;
		});

		// If in console, add expectations for task registration
		if ('console' === $context) {
			$mockBootstrap
				->expects($this->exactly(1))
				->method('registerTasks')
				->with($this->_services['task.collection']);

			$registerMethodOrder[] = 'registerTasks';
		}

		// Set up mock expectations
		$mockBootstrap
			->expects($this->exactly(1))
			->method('registerServices')
			->with($this->_services);

		$mockBootstrap
			->expects($this->exactly(1))
			->method('registerRoutes')
			->with($this->_services['routes']);

		$mockBootstrap
			->expects($this->exactly(1))
			->method('registerEvents')
			->with($this->_services['event.dispatcher']);

		// Add the bootstraps
		$this->_loader
			->add(new Mocks\FauxFullBootstrap)
			->add($methodCallLoggingBootstrap)
			->add($mockBootstrap);

		// Load the bootstraps
		$this->_loader->load();

		// Test that the register methods were called in the correct order
		$this->assertEquals($registerMethodOrder, $methodCallLoggingBootstrap->getCalls());

		// Test the bootstraps were cleared from the loader
		$this->assertEmpty($this->_loader->getBootstraps());
	}
}