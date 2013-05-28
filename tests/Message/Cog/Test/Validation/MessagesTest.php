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
		$this->assertEquals(array(), $this->_messages->get());
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
		$this->_messages->addError('test', 'test error');
		$fields = $this->_messages->get();

		$this->assertEquals('test error', $fields['test'][0]);
	}

	public function testClear()
	{
		$this->_messages->addError('test', 'test error');
		$this->_messages->clear();

		$this->assertEquals(array(), $this->_messages->get());
	}
}