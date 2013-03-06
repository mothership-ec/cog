<?php

namespace Message\Cog\Test\Bootstrap;

use Message\Cog\Bootstrap\Loader;

use Message\Cog\Test\Service\FauxContainer;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	protected $_services;
	protected $_loader;

	public function setUp()
	{
		$this->_services = new FauxContainer;
		$this->_loader   = new Loader($this->_services);
	}

	public function testChainability()
	{
		$this->assertEquals($this->_loader, $this->_loader->add(new FauxServiceBootstrap));
		$this->assertEquals($this->_loader, $this->_loader->addFromDirectory(dirname(__FILE__), 'Not\A\Namespace'));
		$this->assertEquals($this->_loader, $this->_loader->clear());
	}

	public function testAdd()
	{
		$bootstrap = new FauxServiceBootstrap;

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

	public function testAddFromDirectory()
	{
		// test non-php files are skipped
		// test files skipped if class not found
		// test ContainerAware classes dealt with
		// test RequestAware classes dealt with
		// test only added if they implement BootstrapInterface
	}

	public function testLoad()
	{
		// test that registerServices, registerRoutes and registerEvents are all
		// called and passed the correct service
		// also test they run in the correct order
		// test getBootstraps() is empty after (clear is called)

		$this->_loader->add(new FauxServiceBootstrap);
		$this->_loader->add(new MethodCallOrderTesterBootstrap);
		// also add a mock that expects the correct inputs & calls

	}

	public function testLoadTasks()
	{
		// test that registerTasks is called, passed the correct service and is
		// only called when in console context
	}
}