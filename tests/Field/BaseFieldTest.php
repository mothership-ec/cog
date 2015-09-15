<?php

namespace Message\Cog\Test\Field;

use Message\Cog\Field\BaseField;

class BaseFieldTest extends \PHPUnit_Framework_TestCase
{
	protected $_field;

	public function setUp()
	{
		$this->_field = new FauxField;
		$this->_field->setName('my_field');
		$this->_field->setLabel('This is my field');
		$this->_field->setValue('hello');

	}

	public function testLocalisableToggle()
	{
		$this->assertFalse($this->_field->isLocalisable());
		$this->assertSame($this->_field, $this->_field->setLocalisable(true));
		$this->assertTrue($this->_field->isLocalisable());
		$this->assertSame($this->_field, $this->_field->setLocalisable(false));
		$this->assertFalse($this->_field->isLocalisable());
	}

	public function testGetNameAndLabel()
	{
		$this->assertEquals('my_field', $this->_field->getName());
		$this->assertEquals('This is my field', $this->_field->getLabel());
	}

	public function testToString()
	{
		echo $this->_field;

		$this->expectOutputString('hello');
	}
}