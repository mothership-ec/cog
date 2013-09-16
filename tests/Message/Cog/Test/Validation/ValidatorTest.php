<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Validator;
use Message\Cog\Validation\Field;
use Message\Cog\Validation\Messages;
use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Filter\Other as OtherFilter;
use Message\Cog\Validation\Rule\Other as OtherRule;
use Message\Cog\Test\Validation\DummyCollection;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	protected $_messages;
	protected $_validator;
	protected $_loader;

	public function setUp()
	{
		$this->_messages = new Messages;

		$this->_loader = new Loader(
			$this->_messages, array(
				new DummyCollection,
				new OtherFilter,
				new OtherRule
			));


		$this->_validator = new Validator($this->_loader);
	}

	/**
	 * Checks last_name and first_name fields. Should create two error messages:
	 * - non alpha numeric characters in first_name
	 * - last_name is a required field
	 */
	public function testOptional()
	{
		$this->_validator
			->addField(new Field('last_name'))
			->addField(new Field('first_name'))
				->optional()
		;

		$this->_validator->validate(array(
			'first_name' => 'assd64add]asd',
		));

		$this->assertEquals(1, count($this->_validator->getMessages()));

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
		$this->assertEquals($this->_validator, $this->_validator->addField(new Field('test')));
		$this->assertEquals($this->_validator, $this->_validator->addField(new Field('another test', 'Test field')));
	}

	/**
	 * Test exception is thrown if method does not exist
	 */
	public function testInvalidMethodName()
	{
		try {
			$this->_validator
				->addField('first_name')
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
			->addField(new Field('test'))
				->notTestRule()
		;

		$this->_validator->validate(array(
			'test' => 'asdasd'
		));

		$this->assertEquals(1, count($this->_validator->getMessages()));

	}

	/**
	 * Test for when expected data is not validated.
	 * Was getting an undefined index error on the _applyFilters method
	 * when data was passed through a filter.
	 */
	public function testMissingData()
	{
		$this->_validator
			->addField(new Field('test'))
				->testRule()
		;

		$this->_validator->validate(array(
			'not_test' => 'where did it go?'
		));

		$this->assertEquals(1, count($this->_validator->getMessages()));
	}

	public function testCleanData()
	{
		$form = new Field('form');
		$form->children = array('testNested' => new Field('testNested'));

		$this->_validator
			->addField($form)
			->addField(new Field('test'));

		$this->_validator->validate(
			array(
				'test' => 'field',
				'notAfield' => 'bla',
				'form' => array(
					'testNested' => 'nested!',
					'notAfieldEither' => 'nope'
				)
			)
		);

		$expected = array (
			'test' => 'field',
			'form' => array(
				'testNested' => 'nested!'
			)
		);

		$this->assertEquals($expected, $this->_validator->getData());
	}

	/**
	 * Test that adding 'Before' to filter name edits string before it is checked for validity
	 */
	public function testBeforeFilter()
	{
		$this->_validator
			->addField(new Field('field'))
				->isTest()
				->testFilterBefore();

		$this->_validator->validate(
			array('field' => 'lkjaslkdjas')
		);

		$data = $this->_validator->getData();

		$this->assertEquals(0, count($this->_validator->getMessages()));
		$this->assertEquals('test', $data['field']);
	}

	/**
	 * Test that adding 'After' to filter name edits string after it is checked for validity
	 */
	public function testAfterFilter()
	{
		$this->_validator
			->addField(new Field('field'))
				->testFilterAfter()
				->isTest();

		$this->_validator->validate(
			array('field' => 'message.co.uk')
		);

		$data = $this->_validator->getData();

		$this->assertEquals(1, count($this->_validator->getMessages()));
		$this->assertEquals('test', $data['field']);
	}

	/**
	 * Test that custom 'other' filter works
	 */
	public function testOtherFilter()
	{
		$validator2 = new Validator($this->_loader);
		$validator2
			->addField(new Field('test'))
				->filter('md5');

		$form = new Field('form');
		$form->children = $validator2->getFields();

		$this->_validator
			->addField(new Field('test'))
				->filter('md5')
			->addField($form);

		$this->_validator->validate(
			array(
				'test' => 'test',
				'form' => array('test' => 'test'),
			)
		);

		$data = $this->_validator->getData();

		$this->assertTrue(strlen($data['test']) === 32);
		$this->assertEquals(strlen($data['form']['test']), strlen($data['test']));
	}

	public function testOtherRulePass()
	{
		$validator2 = new Validator($this->_loader);
		$validator2
			->addField(new Field('test2'))
				->rule('is_int');

		$form = new Field('form');
		$form->children = $validator2->getFields();

		$this->_validator
			->addField(new Field('test'))
				->rule('is_int')
			->addField($form);

		$this->_validator->validate(
			array('test' => 1, 'form' => array('test2' => 5))
		);

		$this->assertEquals(0, count($this->_validator->getMessages()));
	}

	public function testOtherRuleFail()
	{
		$validator2 = new Validator($this->_loader);
		$validator2
			->addField(new Field('test2'))
				->rule('is_int');

		$form = new Field('form');
		$form->children = $validator2->getFields();

		$this->_validator
			->addField(new Field('test'))
				->rule('is_int')
			->addField($form);


		$this->_validator->validate(
			array('test' => 'abc', 'form' => array('test2' => 'lolol'))
		);

		$this->assertEquals(2, count($this->_validator->getMessages()));
	}

	public function testError()
	{
		$this->_validator
			->addField(new Field('field'))
				->testFail()
				->error('this is an error');

		$this->_validator->validate(
			array('field' => 'data')
		);

		$messages = $this->_validator->getMessages();

		$this->assertEquals('this is an error', $messages['field'][0]);
	}

	/**
	 * Test that getFields returns an array, and includes a field that has been added
	 */
	public function testGetFields()
	{
		$form = new Field('form');
		$form->children = array('test' => new Field('test'));

		$this->_validator
			->addField(new Field('test'))
			->addField($form);

		$fields = $this->_validator->getFields();

		$this->assertTrue(is_array($fields));
		$this->assertTrue(array_key_exists('test', $fields));

		$this->assertEquals(1, count($fields['form']->children));
	}

	public function testGetFieldsEmpty()
	{
		$fields = $this->_validator->getFields();

		$this->assertTrue(is_array($fields));
		$this->assertTrue(empty($fields));
	}

}