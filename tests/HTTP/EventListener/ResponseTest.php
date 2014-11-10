<?php

namespace Message\Cog\Test\HTTP\EventListener;

use Message\Cog\HTTP\EventListener\Response;
use Message\Cog\HTTP\CookieCollection;
use Message\Cog\HTTP\Cookie;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
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
		$this->assertArrayHasKey(KernelEvents::RESPONSE, $subscriptions);
	}

	public function testSetResponseCookies()
	{
		$response = $this->getMock('Message\Cog\HTTP\Response');
		$event    = $this->getMock('Symfony\Component\HttpKernel\Event\FilterResponseEvent', array(
			'getResponse',
			'getRequestType',
		), array(), '', false);

		$event
			->expects($this->any())
			->method('getResponse')
			->will($this->returnValue($response));

		$event
			->expects($this->any())
			->method('getRequestType')
			->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

		$this->_handler->setCookies($event);

		$setCookies = $response->headers->getCookies();

		foreach($this->_cookies as $k => $value) {
			$this->assertSame($value, $setCookies[$k]);
		}
	}

	public function testSetResponseCookiesSubrequest()
	{
		$response = $this->getMock('Message\Cog\HTTP\Response');
		$event    = $this->getMock('Symfony\Component\HttpKernel\Event\FilterResponseEvent', array(
			'getResponse',
			'getRequestType',
		), array(), '', false);

		$event
			->expects($this->any())
			->method('getResponse')
			->will($this->returnValue($response));

		$event
			->expects($this->any())
			->method('getRequestType')
			->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

		$this->assertFalse($this->_handler->setCookies($event));
		$this->assertEmpty($response->headers->getCookies());
	}
}