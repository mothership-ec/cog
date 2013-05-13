<?php

namespace Message\Cog\Test\HTTP\EventListener;

use Message\Cog\HTTP\EventListener\Response;
use Message\Cog\HTTP\Event\Event;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	protected $_handler;

	public function setUp()
	{
		$this->_handler = new Response;
	}

	public function testSubscribedEvents()
	{
		$subscriptions = Response::getSubscribedEvents();

		$this->assertInstanceOf('Message\Cog\Event\SubscriberInterface', $this->_handler);
		$this->assertArrayHasKey(Event::RESPONSE, $subscriptions);
		$this->assertEquals(array(array('prepareResponse')), $subscriptions[Event::RESPONSE]);
	}

	public function testPrepareResponse()
	{
		$request  = $this->getMock('Message\Cog\HTTP\Request');
		$response = $this->getMock('Message\Cog\HTTP\Response', array('prepare'));
		$event    = $this->getMock('Message\Cog\HTTP\Event\FilterResponseEvent', array(
			'getResponse',
			'getRequest',
		), array(), '', false);

		$event
			->expects($this->any())
			->method('getResponse')
			->will($this->returnValue($response));

		$event
			->expects($this->any())
			->method('getRequest')
			->will($this->returnValue($request));

		$response
			->expects($this->exactly(1))
			->method('prepare')
			->with($request);

		$this->_handler->prepareResponse($event);
	}
}