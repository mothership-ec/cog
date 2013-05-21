<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Other;
use Message\Cog\Validation\Loader;

class OtherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Other
	 */
	protected $_filter;

	protected $_messages;
	protected $_loader;

	public function setUp()
	{
		$this->_filter = new Other;

		$this->_messages = $this->getMock('\Message\Cog\Validation\Messages');

		$this->_loader = new Loader($this->_messages, array($this->_filter));
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