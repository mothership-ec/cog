<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Other;
use Message\Cog\Validation\Loader;

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

		$this->_messages = $this->getMock('\Message\Cog\Validation\Messages');

		$this->_loader = new Loader($this->_messages, array($this->_rule));

		$this->_validator = $this->getMock('\Message\Cog\Validation\Validator', array(), array($this->_loader));
	}

	public function testRuleWithNativeFunctions()
	{
		$true = $this->_rule->rule(true, 'is_bool');
		$false = $this->_rule->rule('string', 'is_numeric');

		$this->assertTrue($true);
		$this->assertFalse($false);
	}

	public function testRuleWithNonCallable()
	{
		try {
			$this->assertTrue($this->_rule->rule('var', array()));
			$this->_validator->expects($this->atLeastOnce())
				->method('getData');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}
}