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
		$this->assertEquals($this->_loader, $this->_loader->clear());
	}
}