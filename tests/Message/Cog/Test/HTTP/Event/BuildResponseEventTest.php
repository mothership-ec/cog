<?php

namespace Message\Cog\Test\HTTP\Event;

use Message\Cog\HTTP\Event\BuildResponseEvent;

class BuildResponseEventTest extends \PHPUnit_Framework_TestCase
{
	protected $_event;

	public function setUp()
	{
		$dispatcher = $this->getMock('Message\Cog\HTTP\Dispatcher', array(), array(), '', false);
		$request    = $this->getMock('Message\Cog\HTTP\Request');
		$response   = $this->getMock('Message\Cog\HTTP\Response');

		$this->_event = $this->getMockForAbstractClass('Message\Cog\HTTP\Event\BuildResponseEvent', array($dispatcher, $request));
	}

	public function testResponseSettingAndGetting()
	{
		$response = $this->getMock('Message\Cog\HTTP\Response');

		$this->assertFalse($this->_event->hasResponse());
		$this->assertNull($this->_event->getResponse());

		$this->_event->setResponse($response);

		$this->assertTrue($this->_event->hasResponse());
		$this->assertEquals($response, $this->_event->getResponse());
	}

	public function testSettingResponseStopsPropagation()
	{
		$this->assertFalse($this->_event->isPropagationStopped());

		$this->_event->setResponse(
			$this->getMock('Message\Cog\HTTP\Response')
		);

		$this->assertTrue($this->_event->isPropagationStopped());
	}
}