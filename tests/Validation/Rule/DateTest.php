<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Date;
use Message\Cog\Validation\Loader;

class DateTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Date
	 */
	protected $_rule;

	protected $_messages;
	protected $_loader;

	public function setUp()
	{
		$this->_rule = new Date;

		$this->_messages = $this->getMock('\Message\Cog\Validation\Messages');

		$this->_loader = new Loader($this->_messages, array($this->_rule));
	}

	/**
	 * @large
	 */
	public function testBeforeTrue()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->before($before, $after));
	}

	/**
	 * @large
	 */
	public function testBeforeFalse()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->before($after, $before));
	}

	/**
	 * @large
	 */
	public function testBeforeOrEqualToFalse()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->before($one, $two));
	}

	/**
	 * @large
	 */
	public function testBeforeOrEqualToTrue()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->before($one, $two, true));
	}

	/**
	 * @large
	 */
	public function testBeforeTrueWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimeZone('America/New_York'));
		$this->assertTrue($this->_rule->before($uk, $usa));
	}

	/**
	 * @large
	 */
	public function testBeforeFalseWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimeZone('America/New_York'));
		$this->assertFalse($this->_rule->before($usa, $uk));
	}

	/**
	 * @large
	 */
	public function testAfterTrue()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->after($after, $before));
	}

	/**
	 * @large
	 */
	public function testAfterFalse()
	{
		$before = new \DateTime('01-01-1970');
		$after = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->after($before, $after));
	}

	/**
	 * @large
	 */
	public function testAfterTrueWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimeZone('America/New_York'));
		$this->assertTrue($this->_rule->after($usa, $uk));
	}

	/**
	 * @large
	 */
	public function testAfterFalseWithTimeZone()
	{
		$uk = new \DateTime('31-03-1986', new \DateTimeZone('Europe/London'));
		$usa = new \DateTime('31-03-1986', new \DateTimezone('America/New_York'));
		$this->assertFalse($this->_rule->after($uk, $usa));
	}

	/**
	 * @large
	 */
	public function testAfterOrEqualToFalse()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertFalse($this->_rule->after($one, $two));
	}

	/**
	 * @large
	 */
	public function testAfterOrEqualToTrue()
	{
		$one = new \DateTime('31-03-1986');
		$two = new \DateTime('31-03-1986');
		$this->assertTrue($this->_rule->after($one, $two, true));
	}
}