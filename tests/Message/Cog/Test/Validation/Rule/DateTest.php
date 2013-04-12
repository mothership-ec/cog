<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Date;
use Message\Cog\Validation\Loader;

class DateTest extends \PHPUnit_Framework_TestCase
{
	protected $_rule;

	public function setUp()
	{
		$this->_rule = new Date;
	}

	public function testRegister()
	{
//		@todo get this to work!
//
//		$validator = $this->getMock('\Message\Cog\Validation\Validator');
//		$messages = $this->getMock('\Message\Cog\Validation\Messages');
//
//		$loader = $this->getMockBuilder('\Message\Cog\Validation\Loader')
//			->setMethods('registerRule')
//			->setConstructorArgs(array($validator, $messages))
//			->getMock('\Message\Cog\Validation\Loader')
//		;
//
//		$this->_rule->register($loader);
//
//		$loader->expects($this->atLeastOnce())
//			->method('registerRule')
//			->will($this->returnSelf())
//		;
	}

	public function testBeforeTrue()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->before($before, $after));
	}

	public function testBeforeFalse()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->before($after, $before));
	}

	public function testBeforeOrEqualToFalse()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->before($one, $two));
	}

	public function testBeforeOrEqualToTrue()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->before($one, $two, true));
	}

	public function testBeforeTrueWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimeZone('America/New_York'));
		$this->assertTrue($this->_rule->before($uk, $usa));
	}

	public function testBeforeFalseWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimeZone('America/New_York'));
		$this->assertFalse($this->_rule->before($usa, $uk));
	}

	public function testAfterTrue()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->after($after, $before));
	}

	public function testAfterFalse()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->after($before, $after));
	}

	public function testAfterTrueWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimeZone('America/New_York'));
		$this->assertTrue($this->_rule->after($usa, $uk));
	}

	public function testAfterFalseWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimezone('America/New_York'));
		$this->assertFalse($this->_rule->after($uk, $usa));
	}

	public function testAfterOrEqualToFalse()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->after($one, $two));
	}

	public function testAfterOrEqualToTrue()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->after($one, $two, true));
	}
}