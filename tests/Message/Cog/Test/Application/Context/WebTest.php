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

	public function testRequestServicesDefined()
	{
		$this->assertTrue($this->_container->isShared('http.request.master'));
		$this->assertInstanceOf('Message\Cog\HTTP\Request', $this->_container['http.request.master']);

		$this->assertInstanceOf('Message\Cog\Routing\RequestContext', $this->_container['http.request.context']);
	}

	public function testRunOutputsResponse()
	{
		$dispatcher = $this->getMock(
			'Message\\Cog\\HTTP\\Kernel',
			array('handle', 'send', 'terminate'),
			array(),
			'',
			false
		);

		$response = $this->getMock('Message\\Cog\\HTTP\\Response');

		$dispatcher
			->expects($this->once())
			->method('handle')
			->with($this->_container['http.request.master'])
			->will($this->returnValue($response));

		$response
			->expects($this->once())
			->method('send');

		$dispatcher
			->expects($this->once())
			->method('terminate')
			->with($this->_container['http.request.master'], $response);

		$this->_container['http.kernel'] = function() use ($dispatcher) {
			return $dispatcher;
		};

		$this->_context->run();
	}
}