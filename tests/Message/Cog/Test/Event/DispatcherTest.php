<?php

namespace Message\Cog\Test\Event;

use Message\Cog\Event\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
	public function testContainerIsSet()
	{
		$container = $this->getMock('\\Message\\Cog\\Service\\Container');
		$subscriber = new FauxSubscriber;

		$dispatcher = new Dispatcher($container);
		$dispatcher->addSubscriber($subscriber);

		$this->assertSame($container, $subscriber->getContainer());
	}
}