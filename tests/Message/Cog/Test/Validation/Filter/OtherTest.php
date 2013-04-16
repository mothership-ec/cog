<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Other;

class OtherTest extends \PHPUnit_Framework_TestCase
{
	protected $_filter;

	public function setUp()
	{
		$this->_filter = new Other;
	}

	public function testRegister()
	{

	}

	public function testFilterWithNativeFunctions()
	{
		$this->assertTrue($this->_filter->filter(true, 'is_bool'));
		$this->assertFalse($this->_filter->filter('string', 'is_numeric'));
	}

	public function testFilterWithMethod()
	{
		try {
			$this->assertTrue($this->_filter->filter('var', array()));
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}
}