<?php

namespace Message\Cog\Test\Validation\Check;

use Message\Cog\Validation\Check\Type;

class TypeTest extends \PHPUnit_Framework_TestCase
{
	public function testCheckString()
	{
		$this->assertTrue(Type::checkString('string'));
		$this->assertTrue(Type::checkString('string', '$string'));
	}

	public function testCheckStringFail()
	{
		try {
			Type::checkString(13);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckStringOrNumericWithString()
	{
		$this->assertTrue(Type::checkStringOrNumeric('string'));
		$this->assertTrue(Type::checkStringOrNumeric('string', '$string'));
	}

	public function testCheckStringOrNumericWithInt()
	{
		$this->assertTrue(Type::checkStringOrNumeric(13));
		$this->assertTrue(Type::checkStringOrNumeric(13, '$int'));
	}

	public function testCheckStringOrNumericWithFloat()
	{
		$this->assertTrue(Type::checkStringOrNumeric(1.3));
		$this->assertTrue(Type::checkStringOrNumeric(1.3, '$float'));
	}

	public function testCheckStringOrNumericFail()
	{
		try {
			Type::checkStringOrNumeric(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckInt()
	{
		$this->assertTrue(Type::checkInt(13));
		$this->assertTrue(Type::checkInt(13, '$int'));
	}

	public function testCheckIntFail()
	{
		try {
			Type::checkInt(1.3);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckFloat()
	{
		$this->assertTrue(Type::checkFloat(1.3));
		$this->assertTrue(Type::checkFloat(1.3, '$float'));
	}

	public function testCheckFloatFail()
	{
		try {
			Type::checkFloat(13);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail;
	}

	public function testCheckIntOrFloatWithInt()
	{
		$this->assertTrue(Type::checkIntOrFloat(13));
		$this->assertTrue(Type::checkIntOrFloat(13, '$int'));
	}

	public function testCheckIntOrFloatWithFloat()
	{
		$this->assertTrue(Type::checkIntOrFloat(1.3));
		$this->assertTrue(Type::checkIntOrFloat(1.3, '$float'));
	}

	public function testCheckIntOrFloatFail()
	{
		try {
			Type::checkIntOrFloat('13');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckNumericWithInt()
	{
		$this->assertTrue(Type::checkNumeric(13));
		$this->assertTrue(Type::checkNumeric(13, '$int'));
	}

	public function testCheckNumericWithFloat()
	{
		$this->assertTrue(Type::checkNumeric(1.3));
		$this->assertTrue(Type::checkNumeric(1.3, '$float'));
	}

	public function testCheckNumericWithString()
	{
		$this->assertTrue(Type::checkNumeric('13'));
		$this->assertTrue(Type::checkNumeric('1.3'));
		$this->assertTrue(Type::checkNumeric('13', '$string'));
	}

	public function testCheckNumericFail()
	{
		try {
			Type::checkNumeric('thirteen');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckArray()
	{
		$this->assertTrue(Type::checkArray(array()));
		$this->assertTrue(Type::checkArray(array('hello')));
		$this->assertTrue(Type::checkArray(array('hello', 'world')));
		$this->assertTrue(Type::checkArray(array('hello' => 'world'), '$array'));
		$this->assertTrue(Type::checkArray(array('hello', 'world'), '$array'));
	}

	public function testCheckArrayFail()
	{
		try {
			Type::checkArray('hello');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckBool()
	{
		$this->assertTrue(Type::checkBool(true));
		$this->assertTrue(Type::checkBool(false));
		$this->assertTrue(Type::checkBool(false, '$bool'));
	}

	public function testCheckBoolFail()
	{
		try {
			Type::checkBool(null);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckInstanceOfWithString()
	{
		$object = new \stdClass;
		$this->assertTrue(Type::checkInstanceOf($object, '\stdClass'));
		$this->assertTrue(Type::checkInstanceOf($object, '\stdClass', '$object'));
	}

	public function testCheckInstanceOfWithObject()
	{
		$object = new \stdClass;
		$stdClass = new \stdClass;
		$this->assertTrue(Type::checkInstanceOf($object, $stdClass));
		$this->assertTrue(Type::checkInstanceOf($object, $stdClass, '$object'));
	}

	public function testCheckInstanceOfWithStringFailDifferentObject()
	{
		try {
			$object = new \ArrayObject;
			Type::checkInstanceOf($object, '\stdClass');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckInstanceOfWithObjectFailDifferentObject()
	{
		try {
			$object = new \ArrayObject;
			$stdClass = new \stdClass;
			Type::checkInstanceOf($object, $stdClass);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckInstanceOfWithStringFailInvalidType()
	{
		try {
			$null = null;
			Type::checkInstanceOf($null, '\stdClass');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckInstanceOfWithObjectFailInvalidType()
	{
		try {
			$null = null;
			$stdClass = new \stdClass;
			Type::checkInstanceOf($null, $stdClass);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckObject()
	{
		$this->assertTrue(Type::checkObject(new \stdClass));
		$this->assertTrue(Type::checkObject(new \stdClass, '$obj'));
	}

	public function testCheckObjectFail()
	{
		try {
			Type::checkObject(array());
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testCheckNull()
	{
		$this->assertTrue(Type::checkNull(null));
		$this->assertTrue(Type::checkNull(null, '$null'));
	}

	public function testCheckNullFail()
	{
		try {
			Type::checkNull(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

}