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

		$this->assertSame($response, $event->getResponse());

		$newResponse = $this->getMock('Message\Cog\HTTP\Response', array(), array('hello!', 200));

		$event->setResponse($newResponse);

		$this->assertNotSame($response, $event->getResponse());
		$this->assertSame($newResponse, $event->getResponse());
	}
}