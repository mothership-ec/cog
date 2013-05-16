<?php

namespace Message\Cog\Test\HTTP\Event;

use Message\Cog\HTTP\Event\BuildResponseFromResultEvent;

class BuildResponseFromResultEventTest extends \PHPUnit_Framework_TestCase
{
	public function testGettingAndSettingResponse()
	{
		$dispatcher = $this->getMock('Message\Cog\HTTP\Dispatcher', array(), array(), '', false);
		$request    = $this->getMock('Message\Cog\HTTP\Request');
		$result     = 'Hello! This is a test result.';
		$event      = new BuildResponseFromResultEvent($dispatcher, $request, $result);

		$this->assertEquals($result, $event->getResult());
	}
}