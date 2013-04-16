<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Messages;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
	protected $_messages;

	public function setUp()
	{
		$this->_messages = new Messages;
	}

	public function testGet()
	{

	}

	public function testClear()
	{
		$this->assertEquals($this->_messages, $this->_messages->clear());
	}

	public function testGetSetDefaultErrorMessage()
	{
		$this->_messages->setDefaultErrorMessage('test', 'test message');
		$this->assertEquals('test message', $this->_messages->getDefaultErrorMessage('test'));
	}

	public function testAddFromRule()
	{

	}

	public function testAddError()
	{

	}
}