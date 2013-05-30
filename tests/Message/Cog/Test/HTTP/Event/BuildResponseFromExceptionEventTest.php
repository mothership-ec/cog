<?php

namespace Message\Cog\Test\HTTP\Event;

use Message\Cog\HTTP\Event\BuildResponseFromExceptionEvent;

class BuildResponseFromExceptionEventTest extends \PHPUnit_Framework_TestCase
{
	public function testGettingAndSettingResponse()
	{
		$dispatcher = $this->getMock('Message\Cog\HTTP\Dispatcher', array(), array(), '', false);
		$request    = $this->getMock('Message\Cog\HTTP\Request');
		$exception  = new \Exception('Something broke', 501);
		$event      = new BuildResponseFromExceptionEvent($dispatcher, $request, $exception);

		$this->assertEquals($exception, $event->getException());

		$newException = new \Exception('Another exception');

		$event->setException($newException);

		$this->assertNotEquals($exception, $event->getException());
		$this->assertEquals($newException, $event->getException());
	}
}