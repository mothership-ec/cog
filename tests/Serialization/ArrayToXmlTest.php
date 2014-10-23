<?php

namespace Message\Cog\Test\Serialization;

use Message\Cog\Serialization\ArrayToXml;

class ArrayToXmlTest extends \PHPUnit_Framework_TestCase
{
	const PREFIX = '<?xml version="1.0" encoding="UTF-8" ?>';

	/**
	 * @var ArrayToXml
	 */
	private $_serializer;

	public function setUp()
	{
		$this->_serializer = new ArrayToXml;
	}

	public function testBasicArray()
	{
		$data = [
			'hello' => 'there',
			'good'  => 'bye',
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<xml><hello>there</hello><good>bye</good></xml>';

		$this->assertSame($serialized, $expected);
	}

	public function testWithRoot()
	{
		$data = [
			'data' => [
				'hello' => 'there',
				'good'  => 'bye',
			]
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<data><hello>there</hello><good>bye</good></data>';

		$this->assertSame($serialized, $expected);
	}

	public function testWithBool()
	{
		$data = [
			'yes' => true,
			'no'  => false,
		];

		$serialized = $this->_serializer->serialize($data);
		$expected = self::PREFIX .
			'<xml><yes>true</yes><no>false</no></xml>';

		$this->assertSame($serialized, $expected);
	}

	public function testMultiDimensional()
	{
		$data = [
			'first' => [
				'hello' => 'there',
			],
			'second' => [
				'good' => 'bye',
				'key' => 'value',
			],
		];

		$serialized = $this->_serializer->serialize($data);
		$expected = self::PREFIX .
			'<xml><first><hello>there</hello></first><second><good>bye</good><key>value</key></second></xml>';

		$this->assertSame($serialized, $expected);
	}

	public function testWithNoKeys()
	{
		$data = [
			'hello',
			'there',
		];

		$serialized = $this->_serializer->serialize($data);
		$expected = self::PREFIX .
			'<xml><0>hello</0><1>there</1></xml>';

		$this->assertSame($serialized, $expected);
	}

	public function testSetRoot()
	{
		$this->_serializer->setRoot('food');
		$data = [
			'hello' => 'there',
			'good'  => 'bye',
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<food><hello>there</hello><good>bye</good></food>';

		$this->assertSame($serialized, $expected);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetRootExpection()
	{
		$this->_serializer->setRoot(new \stdClass);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSerializeObject()
	{
		$this->_serializer->serialize(
			['test' => new \stdClass]
		);
	}
}