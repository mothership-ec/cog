<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
	protected $_rule;

	public function setUp()
	{
		$this->_rule = new Date;
	}

	public function beforeTestTrue()
	{
		assertTrue($this->_rule->before(new DateTime('01-01-1970'), new DateTime('31-03-1986')));
	}

	public function afterTest()
	{

	}
}