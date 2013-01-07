<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\Dispatcher;
use Message\Cog\ReferenceParser;
use Message\Cog\Routing\Router;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
	protected $_referenceParser;

	public function setUp()
	{
		// // Generate mock for ReferenceParser
		// $this->_referenceParser = $this->getMockBuilder('Message\Cog\ReferenceParserInterface')
		// 	->disableOriginalConstructor()
		// 	->getMock();

		// $this->_referenceParser
		// 	->expects($this->any())
		// 	->method('parse')
		// 	->will($this->returnValue($this->_referenceParser));

		// $this->_referenceParser
		// 	->expects($this->any())
		// 	->method('getSymfonyLogicalControllerName')
		// 	->will($this->returnValue('Message\ModuleName\Controller\ClassName::viewMethod'));

		// $this->_dispatcher = new Dispatcher($this->_router);
	}

	public function testInternalRoute()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/product/342/gallery');
		$response = $this->_dispatcher->handle($request, Dispatcher::TYPE_INTERNAL);
		$this->assertInstanceOf('Response', $response);
	}

	/**
	 * @expectedException Message\Cog\HTTP\StatusException
	 * @expectedExceptionCode 404
	 */
	public function testExternalRequestThrowsExceptionForInternalRoute()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/product/342/gallery');
		$response = $this->_dispatcher->handle($request, Dispatcher::TYPE_EXTERNAL);
	}

	public function testExternalRoute()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/product/342');
		$response = $this->_dispatcher->handle($request, Dispatcher::TYPE_EXTERNAL);
		$this->assertInstanceOf('Response', $response);
	}

	public function testInternalRequestAllowedForExternalRoute()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/product/342');
		$response = $this->_dispatcher->handle($request, Dispatcher::TYPE_INTERNAL);
		$this->assertInstanceOf('Response', $response);
	}

	/**
	 * @expectedException Message\Cog\HTTP\StatusException
	 * @expectedExceptionCode 406
	 */
	public function testUnknownRequestFormat()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/user/522');
		$request->setRequestFormat('ajnml');
		$response = $this->_dispatcher->handle($request);
	}

	/**
	 * @expectedException Message\Cog\HTTP\StatusException
	 * @expectedExceptionCode 406
	 */
	public function testNotAcceptedRequestFormat()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/user/732');
		$request->setRequestFormat('html');
		$response = $this->_dispatcher->handle($request);
	}


	public function testRequestFormat()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/user/234');
		$request->setRequestFormat('json');
		$response = $this->_dispatcher->handle($request);
		$this->assertInstanceOf('Response', $response);
	}

	public function testFallbackRequestFormat()
	{
		$this->markTestIncomplete('This test needs fixing. It\'s essentially testing the router which it should not');
		$request = Request::create('/user/234');
		$request->setRequestFormat('html|xml'); // html isnt allowed but xml should be fine
		$response = $this->_dispatcher->handle($request);
		$this->assertInstanceOf('Response', $response);
	}
}