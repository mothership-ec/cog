<?php

namespace Message\Cog\Test\HTTP\Event;

use Message\Cog\HTTP\Event\FilterResponseEvent;

class FilterResponseEventTest extends \PHPUnit_Framework_TestCase
{
	public function testGettingAndSettingResponse()
	{
		$dispatcher = $this->getMock('Message\Cog\HTTP\Dispatcher', array(), array(), '', false);
		$request    = $this->getMock('Message\Cog\HTTP\Request');
		$response   = $this->getMock('Message\Cog\HTTP\Response');
		$event      = new FilterResponseEvent($dispatcher, $request, $response);

		$this->assertEquals($response, $event->getResponse());

		$newResponse = $this->getMock('Message\Cog\HTTP\Response', array(), array('hello!', 200));

		$event->setResponse($newResponse);

		$this->assertNotEquals($response, $event->getResponse());
		$this->assertEquals($newResponse, $event->getResponse());
	}
}