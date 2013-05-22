<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Number;
use Message\Cog\Validation\Loader;

class NumberTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Number
	 */
	protected $_rule;

	protected $_messages;
	protected $_loader;

	public function setUp()
	{
		$this->_rule = new Number;

		$this->_messages = $this->getMock('\Message\Cog\Validation\Messages');

		$this->_loader = new Loader($this->_messages, array($this->_rule));
	}

	public function testMinTrue()
	{
		$this->assertTrue($this->_rule->min(3, 1));
	}

	public function testMinFalse()
	{
		$this->assertFalse($this->_rule->min(1, 3));
	}

	public function testMinSame()
	{
		$this->assertTrue($this->_rule->min(1, 1));
	}

	public function testMinNonNumeric()
	{
		try {
			$this->_rule->min(1, false);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testMaxTrue()
	{
		$this->assertTrue($this->_rule->max(1, 3));
	}

	public function testMaxFalse()
	{
		$this->assertFalse($this->_rule->max(3, 1));
	}

	public function testMaxSame()
	{
		$this->assertTrue($this->_rule->max(1, 1));
	}

	public function testMaxNonNumeric()
	{
		try {
			$this->_rule->max(1, false);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testBetweenTrue()
	{
		$this->assertTrue($this->_rule->between(2, 1, 3));
	}

	public function testBetweenFalse()
	{
		$this->assertFalse($this->_rule->between(1, 2, 3));
	}

	public function testBetweenNonNumeric()
	{
		try {
			$this->_rule->between(true, false, array());
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testBetweenMinGreaterThanMax()
	{
		try {
			$this->_rule->between(1, 3, 2);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testMultipleOfTrue()
	{
		$this->assertTrue($this->_rule->multipleOf(6, 6));
		$this->assertTrue($this->_rule->multipleOf(23423, 1));
		$this->assertTrue($this->_rule->multipleOf(99, 11));
	}

	public function testMultipleOfFalse()
	{
		$this->assertFalse($this->_rule->multipleOf(6, 5));
		$this->assertFalse($this->_rule->multipleOf(3, 2));
		$this->assertFalse($this->_rule->multipleOf(100, 150));
	}
}