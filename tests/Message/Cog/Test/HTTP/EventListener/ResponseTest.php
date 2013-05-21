<?php

namespace Message\Cog\Test\HTTP\EventListener;

use Message\Cog\HTTP\EventListener\Response;
use Message\Cog\HTTP\Event\Event;
use Message\Cog\HTTP\CookieCollection;
use Message\Cog\HTTP\Cookie;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	protected $_handler;
	protected $_cookies;

	public function setUp()
	{
		$this->_cookies = array( 
			new Cookie('Test1','this is the first cookie'),
			new Cookie('Test2', 'my second cookie'),
		);

		$cookieCollection = new CookieCollection($this->_cookies);

		$this->_handler = new Response($cookieCollection);
	}

	public function testSubscribedEvents()
	{
		$subscriptions = Response::getSubscribedEvents();

		$this->assertInstanceOf('Message\Cog\Event\SubscriberInterface', $this->_handler);
		$this->assertArrayHasKey(Event::RESPONSE, $subscriptions);
		$this->assertEquals(array(array('setResponseCookies'),array('prepareResponse')), $subscriptions[Event::RESPONSE]);
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
	
	public function testSetResponseCookies()
	{		
		$response = $this->getMock('Message\Cog\HTTP\Response');
		$event = $this->getMock('Message\Cog\HTTP\Event\FilterResponseEvent', array(
			'getResponse',
		), array(), '', false);

		$event
			->expects($this->any())
			->method('getResponse')
			->will($this->returnValue($response));

		$this->_handler->setResponseCookies($event);
		
		$setCookies = $response->headers->getCookies();
		
		foreach($this->_cookies as $k => $value) {
			$this->assertSame($value, $setCookies[$k]);
		}
		
		
	}
}