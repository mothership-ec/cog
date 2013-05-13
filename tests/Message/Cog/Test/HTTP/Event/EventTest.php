<?php

namespace Message\Cog\Test\HTTP\Event;

use Message\Cog\HTTP\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
	public function testEventNames()
	{
		$this->assertEquals('http.request', Event::REQUEST);
		$this->assertEquals('http.response.build', Event::RESPONSE_BUILD);
		$this->assertEquals('http.response', Event::RESPONSE);
		$this->assertEquals('http.exception', Event::EXCEPTION);
	}

	public function testGetters()
	{
		$dispatcher = $this->getMock('Message\Cog\HTTP\Dispatcher', array(), array(), '', false);
		$request    = $this->getMock('Message\Cog\HTTP\Request');
		$event      = new Event($dispatcher, $request);

		$this->assertEquals($dispatcher, $event->getDispatcher());
		$this->assertEquals($request, $event->getRequest());
	}
}