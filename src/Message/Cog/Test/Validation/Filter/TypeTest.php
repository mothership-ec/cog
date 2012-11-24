<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Type;

class TypeTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_filter = new Type;
	}

	public function testString()
	{
		$this->assertSame($this->_filter->string(2344), '2344');
	}

	public function testInteger()
	{
		$this->assertSame($this->_filter->integer('012345'), 12345);
		$this->assertSame($this->_filter->integer(0.32233), 0);
	}

	public function testFloat()
	{
		$this->assertSame($this->_filter->float('3.14'), 3.14);
	}

	public function testBoolean()
	{
		$this->assertSame($this->_filter->boolean('2342'), true);
		$this->assertSame($this->_filter->boolean('0'), false);
		$this->assertSame($this->_filter->boolean(array(1,2,3)), true);
		$this->assertSame($this->_filter->boolean('mike'), true);
	}

	public function testArray()
	{
		$this->assertSame($this->_filter->toArray(123), array(123));
	}

	public function testObject()
	{
		$obj = new \stdClass;
		$obj->foo = 'bar';
		$this->assertEquals($this->_filter->object(array('foo' => 'bar')), $obj);
	}

	public function testDate()
	{
		$this->assertEquals($this->_filter->date('10-10-1985'), new \Datetime('10-10-1985'));
		$this->assertEquals($this->_filter->date('@154654634'), new \Datetime('@154654634'));

		$tz = new \DateTimeZone('Europe/Rome');
		$date = '1st January 2002 11:00';
		$this->assertEquals($this->_filter->date($date, $tz), new \Datetime($date, $tz));
	}

	public function testNull()
	{
		$this->assertNull($this->_filter->null('blackhole'));
	}

}