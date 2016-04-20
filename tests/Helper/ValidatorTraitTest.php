<?php

namespace Message\Cog\Test\Helper;

use Message\Cog\Helper\ValidatorTrait;

interface FooInterface
{}

class Foo implements FooInterface
{
	use ValidatorTrait;
}

class ValidatorTraitTest extends \PHPUnit_Framework_TestCase
{
	private $_foo;

	public function setUp()
	{
		$this->_foo = new Foo;
	}

	public function testCheckNumericInt()
	{
		$this->_foo->checkNumeric(4);
	}

	public function testCheckNumericFloat()
	{
		$this->_foo->checkNumeric(1.1);
	}

	public function testCheckNumericString()
	{
		$this->_foo->checkNumeric('4');
	}

	public function testCheckNumericStringDecimal()
	{
		$this->_foo->checkNumeric('1.3');
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckNumericNonNumeric()
	{
		$this->_foo->checkNumeric('string');
	}

	public function testCheckNumericValidName()
	{
		$this->_foo->checkNumeric(1, 'Foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckNumericInvalidName()
	{
		$this->_foo->checkNumeric(1, []);
	}

	public function testCheckWholeNumberInt()
	{
		$this->_foo->checkWholeNumber(39);
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckWholeNumberFloat()
	{
		$this->_foo->checkWholeNumber(1.1);
	}

	public function testCheckWholeNumberString()
	{
		$this->_foo->checkWholeNumber('3');
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckWholeNumberStringDecimal()
	{
		$this->_foo->checkWholeNumber('1.1');
	}

	public function testCheckWholeNumberValidName()
	{
		$this->_foo->checkWholeNumber(1, 'Foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckWholeNumberInvalidName()
	{
		$this->_foo->checkWholeNumber(1, []);
	}

	public function testCheckScalar()
	{
		$this->_foo->checkScalar(1);
		$this->_foo->checkScalar(1.1);
		$this->_foo->checkScalar('string');
		$this->_foo->checkScalar(null);
		$this->_foo->checkScalar(true);
		$this->_foo->checkScalar(false);
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckScalarWithArray()
	{
		$this->_foo->checkScalar([]);
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckScalarWithObject()
	{
		$this->_foo->checkScalar(new \stdClass);
	}

	public function testCheckScalarWithValidName()
	{
		$this->_foo->checkScalar(true, 'Foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckScalarWithInvalidName()
	{
		$this->_foo->checkScalar(true, []);
	}

	public function testCheckString()
	{
		$this->_foo->checkString('foo');
		$this->_foo->checkString('foo', true);
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckStringWithEmpty()
	{
		$this->_foo->checkString('');
	}

	public function testCheckStringWithEmptyAllowEmpty()
	{
		$this->_foo->checkString('', true);
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckStringWithNonString()
	{
		$this->_foo->checkString(true);
	}


	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckStringWithNonStringAllowEmpty()
	{
		$this->_foo->checkString(false);
	}

	public function testCheckStringWithValidName()
	{
		$this->_foo->checkString('string', false, 'Foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckStringWithInvalidName()
	{
		$this->_foo->checkString('string', false, []);
	}

	public function testCheckInstanceAgainstClassName()
	{
		$this->_foo->checkInstance(new \stdClass, '\\stdClass');
	}

	public function testCheckInstanceAgainstObject()
	{
		$this->_foo->checkInstance(new \stdClass, new \stdClass);
	}

	public function testCheckInstanceAgainstInterface()
	{
		$this->_foo->checkInstance(new Foo, '\\Message\\Cog\\Test\\Helper\\FooInterface');
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckInstanceWithNonObject()
	{
		$this->_foo->checkInstance('foo', new \stdClass);
	}

	/**
	 * @expectedException \Message\Cog\Exception\InvalidVariableException
	 */
	public function testCheckInstanceWithDifferentObject()
	{
		$this->_foo->checkInstance(new Foo, new \stdClass);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testCheckInstanceWithNonExistentClass()
	{
		$this->_foo->checkInstance(new \stdClass, 'FakeClass');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckInstanceAgainstInvalidType()
	{
		$this->_foo->checkInstance(new \stdClass, true);
	}

	public function testCheckInstanceWithValidName()
	{
		$this->_foo->checkInstance(new \stdClass, new \stdClass, 'Foo');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckInstanceWithInvalidName()
	{
		$this->_foo->checkInstance(new \stdClass, new \stdClass, []);
	}
}