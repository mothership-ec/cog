<?php

namespace Message\Cog\Test\HTTP\REST;

use Message\Cog\HTTP\REST\RequestData;

class RequestDataTest extends \PHPUnit_Framework_TestCase
{
	private $_requestData;

	public function setUp()
	{
		$this->_requestData = new RequestData;
	}

	public function testSetMethodPostCaps()
	{
		$method = 'POST';
		$this->_requestData->setMethod($method);
		$this->assertSame($method, $this->_requestData->getMethod());
	}

	public function testSetMethodPostNoCaps()
	{
		$method = 'post';
		$this->_requestData->setMethod($method);
		$this->assertSame(strtoupper($method), $this->_requestData->getMethod());
	}

	public function testSetMethodGetCaps()
	{
		$method = 'GET';
		$this->_requestData->setMethod($method);
		$this->assertSame($method, $this->_requestData->getMethod());
	}

	public function testSetMethodGetNoCaps()
	{
		$method = 'get';
		$this->_requestData->setMethod($method);
		$this->assertSame(strtoupper($method), $this->_requestData->getMethod());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetMethodInvalidArg()
	{
		$method = false;
		$this->_requestData->setMethod($method);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetMethodInvalidMethod()
	{
		$method = 'nope';
		$this->_requestData->setMethod($method);
	}

	public function testSetUrl()
	{
		$url = 'http://message.co.uk';
		$this->_requestData->setUrl($url);
		$this->assertSame($url, $this->_requestData->getUrl());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetUrlInvalidArg()
	{
		$url = false;
		$this->_requestData->setUrl($url);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetUrlInvalidUrl()
	{
		$url = 'this is not a url';
		$this->_requestData->setUrl($url);
	}

	public function testSetData()
	{
		$data = ['this', 'is', 'some', 'data'];
		$this->_requestData->setData($data);
		$this->assertSame($data, $this->_requestData->getData());
	}
}