<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Number;
use Message\Cog\Validation\Loader;

class NumberTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Number
	 */
	protected $_filter;

	protected $_messages;
	protected $_loader;

	public function setUp()
	{
		$this->_filter = new Number;

		$this->_messages = $this->getMock('\Message\Cog\Validation\Messages');

		$this->_loader = new Loader($this->_messages, array($this->_filter));
	}

	public function testAdd()
	{
		$this->assertEquals(10, $this->_filter->add(5, 5));
		$this->assertEquals(155, $this->_filter->add(5, 150));
		$this->assertEquals(3.14, $this->_filter->add(2.1, 1.04));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testAddInvalid()
	{
		$this->_filter->add(4, 'test');
	}

	public function testSubtract()
	{
		$this->assertEquals(5, $this->_filter->subtract(10, 5));
		$this->assertEquals(-5, $this->_filter->subtract(5, 10));
		$this->assertEquals(3, $this->_filter->subtract(3.14, 0.14));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testSubtractInvalid()
	{
		$this->_filter->subtract(3, 'test');
	}

	public function testMultiply()
	{
		$this->assertEquals(2, $this->_filter->multiply(1, 2));
		$this->assertEquals(12, $this->_filter->multiply(4, 3));
		$this->assertEquals(7.5, $this->_filter->multiply(2.5, 3));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testMultiplyInvalid()
	{
		$this->_filter->multiply(3, 'test');
	}

	public function testDivide()
	{
		$this->assertEquals(2.5, $this->_filter->divide(5, 2));
		$this->assertEquals(3, $this->_filter->divide(9, 3));
		$this->assertEquals(25, $this->_filter->divide(25, 1));
		$this->assertEquals(1.25, $this->_filter->divide(2.5, 2));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testDivideInvalid()
	{
		$this->_filter->divide(3, 'test');
	}

	public function testPercentage()
	{
		$this->assertEquals(20, $this->_filter->percentage(5, 25));
		$this->assertEquals(1, $this->_filter->percentage(1, 100));
		$this->assertEquals(50, $this->_filter->percentage(7500, 15000));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testPercentageInvalid()
	{
		$this->_filter->percentage(5, 'test');
	}

}