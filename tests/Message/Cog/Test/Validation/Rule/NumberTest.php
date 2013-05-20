<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Number;

class NumberTest extends \PHPUnit_Framework_TestCase
{
	protected $_rule;

	public function setUp()
	{
		$this->_rule = new Number;
	}

	public function testRegister()
	{

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
}