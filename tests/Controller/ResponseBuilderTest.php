<?php

namespace Message\Cog\Test\Controller;

use Message\Cog\Controller\ResponseBuilder;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class ResponseBuilderTest extends \PHPUnit_Framework_TestCase
{
	public function testRequestSetting()
	{
		$engine = $this->getMock('Message\Cog\Templating\DelegatingEngine');
		$responseBuilder = new ResponseBuilder($engine);

		$this->assertInstanceOf('Message\Cog\HTTP\RequestAwareInterface', $responseBuilder);

		// Test returns $this for chainability
		$this->assertEquals(
			$responseBuilder,
			$responseBuilder->setRequest($this->getMock('Message\Cog\HTTP\Request'))
		);
	}

	public function testRenderReturnsResponse()
	{
		$reference = '::ViewDir:ViewName';
		$content = 'I\'m a little teapot, short and stout. This is my handle. This is my spout.';
		$engine = $this->getMock('Message\Cog\Templating\DelegatingEngine');

		$engine
			->expects($this->exactly(1))
			->method('render')
			->with($reference)
			->will($this->returnValue($content));

		$responseBuilder = new ResponseBuilder($engine);
		$responseBuilder->setRequest($this->getMock('Message\Cog\HTTP\Request'));

		$response = $responseBuilder->render($reference);

		$this->assertInstanceOf('Message\Cog\HTTP\Response', $response);
		$this->assertEquals($content, $response->getContent());
	}

	public function testRenderViewUnbuildableException()
	{
		$reference       = 'Message:ModuleName:ViewName';
		$params          = array('itemID' => 4, 'name' => 'Joe');
		$renderException = new \Exception('Some exception');
		$request         = $this->getMock('Message\Cog\HTTP\Request');
		$engine          = $this->getMock('Message\Cog\Templating\DelegatingEngine');

		$engine
			->expects($this->exactly(1))
			->method('render')
			->with($reference, $params)
			->will($this->throwException($renderException));

		$responseBuilder = new ResponseBuilder($engine);

		// $responseBuilder
		// 	->expects($this->exactly(1))
		// 	->method('_generateResponse')
		// 	->with($params)
		// 	->will($this->returnValue(false));

		$request
			->expects($this->any())
			->method('getAllowedContentTypes')
			->will($this->returnValue(array()));

		$responseBuilder->setRequest($request);

		try {
			$responseBuilder->render($reference, $params);
		}
		catch (NotAcceptableHttpException $e) {
			$this->assertEquals(406, $e->getStatusCode());
			$this->assertEquals($renderException, $e->getPrevious());
			return;
		}

		$this->fail('Exception should be raised when no view can be rendered or generated');
	}
}