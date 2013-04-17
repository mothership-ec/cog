<?php

namespace Message\Cog\Test\Validation\Rule;

use Message\Cog\Validation\Rule\Iterable;
use Message\Cog\Validation\Validator;

class IterableTest extends \PHPUnit_Framework_TestCase
{

	protected $_rule;
	protected $_validator;

	public function setUp()
	{
		$this->_rule = new Iterable;
		$this->_validator = new Validator;
	}

	/**
	 * Test true with sequential array
	 */
	public function testEachTrueSeqArray()
	{
		$data = array(1, 2);
		$this->assertTrue($this->_rule->each($data, 'is_int'));
	}

	/**
	 * Test true with associative array
	 */
	public function testEachTrueAssocArray()
	{
		$data = array(
			'hello' => 'hello',
			'world' => 'world',
		);

		$this->assertTrue($this->_rule->each($data, 'strstr'));
	}

	/**
	 * Test false with sequential array
	 */
	public function testEachFalseSeqArray()
	{
		$data = array(1, 'two');

		$this->assertFalse($this->_rule->each($data, 'is_int'));
	}

	/**
	 * Test false with associative array
	 */
	public function testEachFalseAssocArray()
	{
		$data = array(
			'hello' => 'hello',
			'world' => 'hello'
		);

		$this->assertFalse($this->_rule->each($data, 'strstr'));
	}

	public function testValidateEachTrue()
	{
		$callback = function(Validator $val){
			$val
//				->field('example')
				->field('example_two');
			return $val;
		};

		$data = array(
			array(
//				'example' => 'test',
				'example_two' => 0 // was a bug where if zero was passed it would return false
			),
		);

		$this->assertTrue($this->_rule->validateEach($data, $callback));
	}

	public function testValidateEachFalse()
	{
		$callback = function(Validator $val){
			$val->field('example');
			return $val;
		};

		$data = array(
			array(
				'example_two' => 'test',
			),
		);

		$this->assertFalse($this->_rule->validateEach($data, $callback));
	}

	/**
	 * Test that exception is thrown when $func does not return instance of Validator
	 */
	public function testValidateEachFail()
	{
		try {
			$data = array(1, 2);
			$this->_rule->validateEach($data, 'is_int');
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

}