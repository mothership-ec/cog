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
		$this->assertSame('2344', $this->_filter->string(2344));
	}

	public function testIntegerFromString()
	{
		$this->assertSame(12345, $this->_filter->integer('012345'));
	}

	public function testIntegerFromFloatRoundUp()
	{
		$this->assertSame(1, $this->_filter->integer(0.76677));
	}

	public function testIntegerFromFloatRoundDown()
	{
		$this->assertSame(0, $this->_filter->integer(0.32233));
	}

	public function testIntegerFromFloatForceRoundUp()
	{
		$this->assertSame(1, $this->_filter->integer(0.32233, 'up'));
	}

	public function testIntegerFromFloatForceRoundDown()
	{
		$this->assertSame(0, $this->_filter->integer(0.76677, 'down'));
	}

	public function testFloatFromString()
	{
		$this->assertSame(3.14, $this->_filter->float('3.14'));
	}

	public function testFloatFromInt()
	{
		$this->assertSame(1.0, $this->_filter->float(1));
	}

	public function testBooleanFromString()
	{
		$this->assertSame(true, $this->_filter->boolean('2342'));
		$this->assertSame(true, $this->_filter->boolean('mike'));
		$this->assertSame(false, $this->_filter->boolean('0'));
		$this->assertSame(false, $this->_filter->boolean(''));
	}

	public function testBooleanFromArray()
	{
		$this->assertSame(true, $this->_filter->boolean(array(1, 2, 3)));
		$this->assertSame(true, $this->_filter->boolean(array(123)));
		$this->assertSame(false, $this->_filter->boolean(array()));
	}

	public function testBooleanFromInt()
	{
		$this->assertSame(true, $this->_filter->boolean(1));
		$this->assertSame(true, $this->_filter->boolean(123));
		$this->assertSame(false, $this->_filter->boolean(0));
	}

	public function testBooleanFromFloat()
	{
		$this->assertSame(true, $this->_filter->boolean(1.2));
		$this->assertSame(false, $this->_filter->boolean(0.0));
	}

	public function testBooleanFromNull()
	{
		$this->assertSame(false, $this->_filter->boolean(null));
	}

	public function testArrayFromString()
	{
		$this->assertSame(array('prince'), $this->_filter->toArray('prince'));
	}

	public function testArrayFromInt()
	{
		$this->assertSame(array(123), $this->_filter->toArray(123));
	}

	public function testArrayObjectFromArray()
	{
		$arrayObject = $this->_filter->toArrayObject(array('hello'));
		$this->assertInstanceOf('\ArrayObject', $arrayObject);
		$this->assertEquals('hello', $arrayObject[0]);
	}

	public function testArrayObjectFromString()
	{
		$arrayObject = $this->_filter->toArrayObject('hello');
		$this->assertInstanceOf('\ArrayObject', $arrayObject);
		$this->assertEquals('hello', $arrayObject[0]);
	}

	public function testObject()
	{
		$obj = new \stdClass;
		$obj->foo = 'bar';
		$this->assertEquals($obj, $this->_filter->object(array('foo' => 'bar')));
	}

	public function testDateNoTimeZone()
	{
		$this->assertEquals(new \Datetime('10-10-1985'), $this->_filter->date('10-10-1985'));
	}

	public function testDateWithDateTimeZone()
	{
		$tz = new \DateTimeZone('Europe/Rome');
		$date = '1st January 2002 11:00';
		$this->assertEquals(new \Datetime($date, $tz), $this->_filter->date($date, $tz));
	}

	public function testDateWithStringTimeZone()
	{
		$tz = 'Europe/Rome';
		$date = '1st January 2002 11:00';
		$this->assertEquals(new \DateTime($date, new \DateTimeZone($tz)), $this->_filter->date($date, $tz));
	}

	/**
	 * Test to check if an exception is thrown when given a timezone that doesn't exist
	 */
	public function testDateInvalidTimeZone()
	{
		try {
			$this->_filter->date('10-10-1985', 'asdasdad');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	/**
	 * Test to check if an exception is thrown when given an invalid timezone argument i.e. not an instance of \DateTimeZone or a string
	 */
	public function testDateInvalidTimeZoneDataType()
	{
		try {
			$this->_filter->date('10-10-1985', true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testNull()
	{
		$this->assertNull($this->_filter->null('blackhole'));
	}

}