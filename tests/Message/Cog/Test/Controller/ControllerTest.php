<?php

namespace Message\Cog\Test\Controller;

use Message\Cog\Controller\Controller;
use Message\Cog\Test\Service\FauxContainer;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
	protected $_controller;

	public function setUp()
	{
		$this->_controller = new Controller;
	}

	public function testContainerAndRequestAware()
	{
		$this->assertInstanceOf('Message\\Cog\\Service\\ContainerAwareInterface', $this->_controller);
		$this->assertInstanceOf('Message\\Cog\\HTTP\\RequestAwareInterface', $this->_controller);
	}

	public function testGenerateUrl()
	{
		$routeName = 'frontend.testroute.view';
		$params    = array('var' => 'yeah');
		$returnVal = 'http://www.website.com/path/to/file';
		$router    = $this->getMock('Message\\Cog\\Test\\Routing\\FauxRouter');
		$container = new FauxContainer;

		// Set up expectations
		$router
			->expects($this->exactly(1))
			->method('generate')
			->with($routeName, $params)
			->will($this->returnValue($returnVal));

		$container['router'] = $container->share(function() use ($router) {
			return $router;
		});

		$this->_controller->setContainer($container);
		$this->assertEquals($returnVal, $this->_controller->generateUrl($routeName, $params));
	}

	public function testRedirect()
	{
		$response = $this->_controller->redirect('google.com', 302);

		$this->assertInstanceOf('Message\\Cog\\HTTP\\RedirectResponse', $response);
		$this->assertEquals(302, $response->getStatusCode());

		$response = $this->_controller->redirect('message.co.uk', 301);

		$this->assertInstanceOf('Message\\Cog\\HTTP\\RedirectResponse', $response);
		$this->assertEquals(301, $response->getStatusCode());
	}

	public function testForward()
	{
		$routeName  = 'my.special.route';
		$attribs    = array('id' => 6, 'variable' => 'value');
		$query      = array('page' => 4);
		$returnVal  = $this->getMock('Message\\Cog\\HTTP\\Response');
		$dispatcher = $this->getMock('Message\\Cog\\HTTP\\Dispatcher', array(), array(), '', false);
		$container  = new FauxContainer;

		// Set up expectations
		$dispatcher
			->expects($this->exactly(1))
			->method('forward')
			->with($routeName, $attribs, $query)
			->will($this->returnValue($returnVal));

		$container['http.dispatcher'] = $container->share(function() use ($dispatcher) {
			return $dispatcher;
		});

		$this->_controller->setContainer($container);
		$this->assertEquals($returnVal, $this->_controller->forward($routeName, $attribs, $query));
	}

	public function testRender()
	{
		$reference       = 'Message:ModuleName:ViewName:ViewFile';
		$params          = array('orderID' => 5);
		$request         = $this->getMock('Message\\Cog\\HTTP\\Request');
		$container       = new FauxContainer;
		$responseBuilder = $this->getMock('Message\\Cog\\Controller\\ResponseBuilder', array(), array(), '', false);
		$returnVal       = $this->getMock('Message\\Cog\\HTTP\\Response');

		$responseBuilder
			->expects($this->exactly(1))
			->method('setRequest')
			->with($request)
			->will($this->returnValue($responseBuilder));

		$responseBuilder
			->expects($this->exactly(1))
			->method('render')
			->with($reference, $params)
			->will($this->returnValue($returnVal));

		$container['response_builder'] = $container->share(function() use ($responseBuilder) {
			return $responseBuilder;
		});

		$this->_controller->setContainer($container);
		$this->_controller->setRequest($request);
		$this->assertEquals($returnVal, $this->_controller->render($reference, $params));
	}

	/**
	 * @expectedException        \LogicException
	 * @expectedExceptionMessage Request must be set
	 */
	public function testRenderWithNoRequestException()
	{
		$this->_controller->render('::SomeView');
	}
}