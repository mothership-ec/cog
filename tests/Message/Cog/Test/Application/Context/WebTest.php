<?php

namespace Message\Cog\Test\Application\Context;

use Message\Cog\Application\Context\Web;
use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\HTTP\FauxDispatcher;

class WebTest extends \PHPUnit_Framework_TestCase
{
	protected $_container;
	protected $_context;

	public function setUp()
	{
		$this->_container = new FauxContainer;
		$this->_context   = new Web($this->_container);
	}

	public function testMasterRequestServiceDefined()
	{
		$this->assertTrue($this->_container->isShared('http.request.master'));
		$this->assertInstanceOf('Message\Cog\HTTP\Request', $this->_container['http.request.master']);
	}

	public function testRunOutputsSomething()
	{
		$dispatcher = $this->getMock(
			'Message\Cog\HTTP\Dispatcher',
			array('handle', 'send'),
			array(),
			'',
			false
		);

		$dispatcher
			->expects($this->once())
			->method('handle')
			->with($this->equalTo($this->_container['http.request.master']))
			->will($this->returnValue($dispatcher));

		$dispatcher
			->expects($this->once())
			->method('send');

		$this->_container['http.dispatcher'] = function() use ($dispatcher) {
			return $dispatcher;
		};

		$this->_context->run();
	}
}