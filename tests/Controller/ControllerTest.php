<?php

namespace Message\Cog\Test\Controller;

use Message\Cog\Controller\Controller;
use Message\Cog\HTTP\Request;

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
		$generator = $this->getMock('Message\\Cog\\Routing\\UrlGenerator', array('generate'), array(), '', false);
		$container = new FauxContainer;

		// Set up expectations
		$generator
			->expects($this->exactly(1))
			->method('generate')
			->with($routeName, $params)
			->will($this->returnValue($returnVal));

		$container['routing.generator'] = $container->share(function() use ($generator) {
			return $generator;
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
		$reference  = '::Some:Controller#method';
		$attribs    = array('id' => 6, 'variable' => 'value');
		$query      = array('page' => 4);
		$returnVal  = $this->getMock('Message\\Cog\\HTTP\\Response');
		$kernel = $this->getMock('Message\\Cog\\HTTP\\Kernel', array(), array(), '', false);
		$container  = new FauxContainer;
		$request = $this->getMock(
			'Message\\Cog\\HTTP\\Request',
			array('duplicate'),
			array(array(), array(), array('_format' => 'xml'))
		);
		$subRequest = $this->getMock(
			'Message\\Cog\\HTTP\\Request',
			array('duplicate')
		);
		$parser = $this->getMock(
			'Message\\Cog\\ReferenceParser',
			array('parse', 'getSymfonyLogicalControllerName'),
			array(),
			'',
			false
		);

		$request
			->expects($this->exactly(1))
			->method('duplicate')
			->with($query, null, array_merge($attribs, array('_format' => 'xml', '_controller' => 'ControllerReference')))
			->will($this->returnValue($subRequest));

		$parser
			->expects($this->any())
			->method('parse')
			->with($reference)
			->will($this->returnValue($parser));

		$parser
			->expects($this->any())
			->method('getSymfonyLogicalControllerName')
			->will($this->returnValue('ControllerReference'));

		$kernel
			->expects($this->exactly(1))
			->method('handle')
			->with($subRequest, $kernel::SUB_REQUEST, false)
			->will($this->returnValue($returnVal));

		$container['request'] = function() use ($request) {
			return $request;
		};

		$container['reference_parser'] = $container->share(function() use ($parser) {
			return $parser;
		});

		$container['http.kernel'] = $container->share(function() use ($kernel) {
			return $kernel;
		});

		$this->_controller->setContainer($container);

		$this->assertEquals($returnVal, $this->_controller->forward($reference, $attribs, $query, false));
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