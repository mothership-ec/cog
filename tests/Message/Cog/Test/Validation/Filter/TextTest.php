<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_filter = new Text;
	}

	public function testUppercase()
	{
		$this->assertEquals($this->_filter->uppercase('nelson'), 'NELSON');
	}

	public function testPrefix()
	{
		$this->assertEquals($this->_filter->prefix('angry', 'very '), 'very angry');
	}

	public function testSuffix()
	{
		$this->assertEquals($this->_filter->suffix('hill', 'side'), 'hillside');
	}
}