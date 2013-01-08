<?php

namespace Message\Cog\Test\Validation\Rule;

class TextTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_rule = new Text;
	}

	public function testAlnum()
	{
		$this->assertTrue($this->_rule->alnum('0123456789'));
		$this->assertTrue($this->_rule->alnum('aBcdeFGhiJk'));
		$this->assertTrue($this->_rule->alnum('2aSf234saF3d'));
		$this->assertFalse($this->_rule->alnum('sad75-@'));
	}

	public function testLength()
	{
		$this->assertFalse($this->_rule->length('green', 3));
		$this->assertTrue($this->_rule->length('red', 3));
		$this->assertTrue($this->_rule->length('blue', 1, 10));
		$this->assertFalse($this->_rule->length('turquoise', 5, 6));
		$this->assertFalse($this->_rule->length('red', 6, 20));
	}
}