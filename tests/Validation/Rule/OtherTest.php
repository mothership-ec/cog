<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Other;
use Message\Cog\Validation\Loader;
use Message\Cog\Test\Validation\DummyCollection;

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

	public function testRuleWithMethod()
	{
		$callable = array(
			new DummyCollection,
			'isTest'
		);
		$this->assertTrue($this->_rule->rule('test', $callable));
		$this->assertFalse($this->_rule->rule('not test', $callable));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testRuleWithNonCallable()
	{
		$this->_rule->rule('var', array());
	}
}