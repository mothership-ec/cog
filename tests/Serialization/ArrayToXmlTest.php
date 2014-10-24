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

	public function testSerializeBasicArray()
	{
		$data = [
			'hello' => 'there',
			'good'  => 'bye',
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<xml><hello>there</hello><good>bye</good></xml>';

		$this->assertSame($expected, $serialized);
	}

	public function testSerializeWithRoot()
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

		$this->assertSame($expected, $serialized);
	}

	public function testSerializeWithBool()
	{
		$data = [
			'yes' => true,
			'no'  => false,
		];

		$serialized = $this->_serializer->serialize($data);
		$expected = self::PREFIX .
			'<xml><yes>true</yes><no>false</no></xml>';

		$this->assertSame($expected, $serialized);
	}

	public function testSerializeMultiDimensional()
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

		$this->assertSame($expected, $serialized);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSerializeWithNoKeys()
	{
		$data = [
			'hello',
			'there',
		];

		$this->_serializer->serialize($data);
	}

	public function testSerializeWithSetRoot()
	{
		$this->_serializer->setRoot('food');
		$data = [
			'hello' => 'there',
			'good'  => 'bye',
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<food><hello>there</hello><good>bye</good></food>';

		$this->assertSame($expected, $serialized);
	}

	public function testSerializeWithMultipleSimilarElements()
	{
		$data = [
			'hello' => [
				'there',
				'everyone',
			],
			'goodbye' => [
				'bye',
			]
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<xml><hello>there</hello><hello>everyone</hello><goodbye>bye</goodbye></xml>';

		$this->assertSame($expected, $serialized);
	}

	public function testSerializeWithMultipleSimilarElementsMultiDimensional()
	{
		$data = [
			'hello' => [
				['there' => 'here'],
				['everyone' => 'everything'],
			],
			'good' => 'bye',
		];

		$serialized = $this->_serializer->serialize($data);
		$expected   = self::PREFIX .
			'<xml><hello><there>here</there></hello><hello><everyone>everything</everyone></hello><good>bye</good></xml>';

		$this->assertSame($expected, $serialized);
	}

	public function testDeserializeBasicArray()
	{
		$xml = self::PREFIX .
			'<xml><hello>there</hello><good>bye</good></xml>';

		$expected = [
			'hello' => 'there',
			'good'  => 'bye',
		];
		$deserialized = $this->_serializer->deserialize($xml);

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeWithRoot()
	{
		$xml = self::PREFIX .
			'<data><hello>there</hello><good>bye</good></data>';

		$deserialized = $this->_serializer->deserialize($xml);
		$expected = [
			'data' => [
				'hello' => 'there',
				'good'  => 'bye',
			]
		];

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeWithBool()
	{
		$data = self::PREFIX .
			'<xml><yes>true</yes><no>false</no></xml>';

		$deserialized = $this->_serializer->deserialize($data);
		$expected = [
			'yes' => true,
			'no'  => false,
		];

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeMultiDimensional()
	{
		$xml = self::PREFIX .
			'<xml><first><hello>there</hello></first><second><good>bye</good><key>value</key></second></xml>';

		$deserialized = $this->_serializer->deserialize($xml);
		$expected = [
			'first' => [
				'hello' => 'there',
			],
			'second' => [
				'good' => 'bye',
				'key' => 'value',
			],
		];

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeInteger()
	{
		$xml = self::PREFIX .
			'<xml><one>1</one></xml>';

		$deserialized = $this->_serializer->deserialize($xml);
		$expected = [
			'one' => (int) 1,
		];

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeFloat()
	{
		$xml = self::PREFIX .
			'<xml><onepointone>1.1</onepointone></xml>';

		$deserialized = $this->_serializer->deserialize($xml);
		$expected = [
			'onepointone' => (float) 1.1,
		];

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeWithSimpleXMLElement()
	{
		$xml = new \SimpleXMLElement(self::PREFIX .
			'<xml><hello>there</hello><good>bye</good></xml>');

		$expected = [
			'hello' => 'there',
			'good'  => 'bye',
		];
		$deserialized = $this->_serializer->deserialize($xml);

		$this->assertSame($expected, $deserialized);
	}

	public function testDeserializeNoPrefix()
	{
		$xml = '<xml><hello>there</hello><good>bye</good></xml>';

		$expected = [
			'hello' => 'there',
			'good'  => 'bye',
		];
		$deserialized = $this->_serializer->deserialize($xml);

		$this->assertSame($expected, $deserialized);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testDeserializeWithNoKeys()
	{
		$xml = self::PREFIX .
			'<xml><0>hello</0><1>there</1></xml>';

		$this->_serializer->deserialize($xml);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testDeserializeInvalidXML()
	{
		$this->_serializer->deserialize('blasdjlasijda');
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testDeserializeNonXML()
	{
		$this->_serializer->deserialize(new \stdClass);
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