<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Other;

class OtherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Other
	 */
	protected $_rule;

	protected $_loader;

	protected $_messages;

	protected $_validator;

	public function setUp()
	{
		$this->_rule = new Other;

		$this->_validator = $this->getMockBuilder('\Message\Cog\Validation\Validator')
			->setMethods(array('getData'))
			->getMock();

		$this->_messages = $this->getMockBuilder('\Message\Cog\Validation\Messages')
			->getMock();

		$this->_loader = $this->getMockBuilder('\Message\Cog\Validation\Loader')
			->getMock();
	}

	public function testRegister()
	{
		$this->_rule->register($this->_loader);
	}

	public function testRuleWithNativeFunctions()
	{
//		$this->_rule->rule(true, 'is_bool');
//		$this->_rule->rule('string', 'is_numeric');
//
//		$this->_validator->expects($this->atLeastOnce())
//			->method('getData');
	}

	public function testRuleWithNonString()
	{
//		try {
//			$this->assertTrue($this->_rule->rule('var', array()));
//			$this->_validator->expects($this->atLeastOnce())
//				->method('getData');
//		}
//		catch (\Exception $e) {
//			return;
//		}
//		$this->fail('Exception not thrown');
	}
}