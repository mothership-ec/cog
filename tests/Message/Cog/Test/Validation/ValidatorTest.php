<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	protected $_validator;

	public function setUp()
	{
		$this->_validator = new Validator;
	}

	/**
	 * Checks last_name and first_name fields. Should create two error messages:
	 * - non alpha numeric characters in first_name
	 * - no last_name set, which is a required field
	 */
	public function testGetMessages()
	{
		$this->_validator
			->field('last_name') 
			->field('first_name')
			->optional()
			->alnum()
		;

		$this->_validator->validate(array(
			'first_name' => 'assd64add]asd',
		));

		$this->assertEquals(2, count($this->_validator->getMessages()));

	}

	public function testGetLoader()
	{
		$this->assertInstanceOf('\Message\Cog\Validation\Loader', $this->_validator->getLoader());
	}

	/**
	 * Test fields can be added without causing fatal errors. Also that they return
	 * an instance of the validator to allow for a fluent interface.
	 */
	public function testField()
	{
		$this->assertEquals($this->_validator, $this->_validator->field('test'));
		$this->assertEquals($this->_validator, $this->_validator->field('another test', 'Test field'));
	}

	/**
	 * Test exception is thrown if method does not exist
	 */
	public function testInvalidMethodName()
	{
		try {
			$this->_validator
				->field('first_name')
				->khaskd();
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	/**
	 * Test method name is inverted if proceeded with 'not'
	 */
	public function testNotMethod()
	{
		$this->_validator
			->field('test')
			->notAlnum()
		;

		$this->_validator->validate(array(
			'test' => 'asdasd'
		));

		$this->assertEquals(1, count($this->_validator->getMessages()));

		$this->_validator->validate(array(
			'test' => '@£4'
		));

		$this->assertEquals(0, count($this->_validator->getMessages()));

	}

	/**
	 * Test for when expected data is not validated.
	 * Was getting an undefined index error on the _applyFilters method
	 * when data was passed through a filter.
	 */
	public function testMissingData()
	{
		$this->_validator
			->field('test')
			->alnum()
		;

		$this->_validator->validate(array(
			'not_test' => 'where did it go?'
		));

	}

	/**
	 * Test that adding 'Before' to filter name edits string before it is checked for validity
	 */
	public function testBeforeFilter()
	{
		$this->_validator
			->field('test')
			->toUrlBefore()
			->url();

		$this->_validator->validate(
			array('test' => 'message.co.uk')
		);

		$data = $this->_validator->getData();

		$this->assertEquals(0, count($this->_validator->getMessages()));
		$this->assertEquals('http://message.co.uk', $data['test']);
	}

	/**
	 * Test that adding 'After' to filter name edits string after it is checked for validity
	 */
	public function testAfterFilter()
	{
		$this->_validator
			->field('test')
			->toUrlAfter()
			->url();

		$this->_validator->validate(
			array('test' => 'message.co.uk')
		);

		$data = $this->_validator->getData();

		$this->assertEquals(1, count($this->_validator->getMessages()));
		$this->assertEquals('http://message.co.uk', $data['test']);
	}

	/**
	 * Test that getFields returns an array, and includes a field that has been added
	 */
	public function testGetFields()
	{
		$this->_validator
			->field('test');

		$fields = $this->_validator->getFields();

		$this->assertTrue(is_array($fields));
		$this->assertTrue(array_key_exists('test', $fields));
	}

	public function testGetFieldsEmpty()
	{
		$fields = $this->_validator->getFields();

		$this->assertTrue(is_array($fields));
		$this->assertTrue(empty($fields));
	}

}