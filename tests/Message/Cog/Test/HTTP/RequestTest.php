<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
	public function testScope()
	{
		$request = new Request;

		$this->assertTrue($request->isExternal());
		$this->assertFalse($request->isInternal());

		$request->setInternal();

		$this->assertFalse($request->isExternal());
		$this->assertTrue($request->isInternal());
	}

	public function testGetAllowedContentTypes()
	{
		$types   = array('application/json', 'text/html');
		$request = new Request(array(), array(), array('_allowedContentTypes' => $types));

		$this->assertEquals($types, $request->getAllowedContentTypes());
	}

	public function testGetAllowedContentTypesFallback()
	{
		$request = $this->getMock('Message\Cog\HTTP\Request', array('getAcceptableContentTypes'));
		$return  = array('test');

		$request
			->expects($this->exactly(1))
			->method('getAcceptableContentTypes')
			->will($this->returnValue($return));

		$this->assertEquals($return, $request->getAllowedContentTypes());
	}
}