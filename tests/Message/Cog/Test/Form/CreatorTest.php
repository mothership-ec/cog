<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Form\Handler;

/**
 * Class HandlerTest
 * @package Message\Cog\Test\Form
 */

class HandlerTest extends \PHPUnit_Framework_TestCase
{
	protected $_handler;

	protected $_builder;

	protected $_engine;

	protected $_helper;

	protected $_container;

	protected $_form;

	protected $_validator;

	protected $_request;

	protected $_config;

	protected $_dispatcher;

	public function setUp()
	{
		// Instanciate container
		$container = new \Message\Cog\Test\Service\FauxContainer;

		// Mock form builder dependencies
		$dispatcher = $this->getMock('\\Symfony\\Component\\EventDispatcher\\EventDispatcher');
		$resolvedFormTypeFactory = $this->getMock('\\Symfony\\Component\\Form\\ResolvedFormTypeFactory');

		$registry = $this->getMock(
			'\\Symfony\\Component\\Form\\FormRegistry',
			array(),
			array(
				array(),
				$resolvedFormTypeFactory
			));

		$factory = $this->getMock(
			'\\Symfony\\Component\\Form\\FormFactory',
			array(),
			array(
				$registry,
				$resolvedFormTypeFactory
			));

		// Add form builder mock to container
		$this->_builder = $this->getMock(
			'\\Symfony\\Component\\Form\\FormBuilder',
			array(),
			array(
				'form',
				'\\Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\PropertyPathMapper',
				$dispatcher,
				$factory
			));

		$container['form.builder'] = $this->_builder;

		// Mock rendering engine
		$this->_engine = $this->getMockBuilder('\\Message\\Cog\\Templating\\PhpEngine')
			->disableOriginalConstructor()
			->setMethods(array('addHelpers'))
			->getMock();

		$container['templating.engine.php'] = $this->_engine;

		// Mock helpers
		$this->_helper = $this->getMockBuilder('\\Message\\Cog\\Form\\Template\\Helper')
			->disableOriginalConstructor()
			->getMock();

		$container['form.helper.php'] = $this->_helper;
		$container['form.helper.twig'] = $this->_helper;

		// Add validator mock to container
		$messages = $this->getMock('\\Message\\Cog\\Validation\\Messages');
		$loader = $this->getMock('\\Message\\Cog\\Validation\\Loader', array(), array($messages));
		$this->_validator = $this->getMock(
			'\\Message\\Cog\\Validation\\Validator',
			array(
				'field',
				'validate',
				'getData',
				'getMessages'
			),
			array($loader));

		$container['validator'] = $this->_validator;

		// Mock request
		$this->_request = $this->getMock('\\Message\\Cog\\HTTP\\Request');
		$container['request'] = $this->_request;

		$this->_container = $container;

		// Mock form
		$this->_dispatcher = $this->getMockBuilder('\\Message\\Cog\\Event\\Dispatcher')
			->disableOriginalConstructor()
			->setMethods(array('hasListeners'))
			->getMock();

		$this->_config = $this->getMockBuilder('\\Symfony\\Component\\Form\\FormConfigBuilder')
			->disableOriginalConstructor()
			->getMock();

		$this->_form = $this->getMockBuilder('\\Symfony\\Component\\Form\\Form')
			->setConstructorArgs(array($this->_config))
			->setMethods(array())
			->getMock();

		// Construct tests
		$this->_config->expects($this->any())
			->method('getEventDispatcher')
			->will($this->returnValue($this->_dispatcher));

		$this->_handler = new Handler($container);
		$this->_handler->setForm($this->_form);
	}

	public function testClear()
	{
		$this->_builder->expects($this->once())
			->method('getForm')
			->will($this->returnValue($this->_form));

		$this->_handler->clear();

		$this->assertEquals($this->_form, $this->_handler->getForm());
		$this->assertEquals($this->_validator, $this->_handler->getValidator());
	}

	public function testAddStringChildOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add('test');
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddObjectChildOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add($this->_form);
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddStringWithType()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add('test', 'select');
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddObjectWithType()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add($this->_form, 'select');
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddStringWithTypeOptions()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add('test', 'select', array('option' => true));
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddObjectWithTypeOptions()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add($this->_form, 'select', array('option' => true));
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddStringWithOptionsOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add('test', null, array('option' => true));
		$this->assertSame($handler, $this->_handler);
	}

	public function testAddObjectWithOptionsOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$handler = $this->_handler->add($this->_form, null, array('option' => true));
		$this->assertSame($handler, $this->_handler);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddInvalidChild()
	{
		$this->_form->expects($this->never())
			->method('add');

		$this->_validator->expects($this->never())
			->method('field');

		$this->_handler->add(null);
	}

	public function testVal()
	{
		$this->assertInstanceOf('\\Message\\Cog\\Validation\\Validator', $this->_handler->val());
	}

	public function testSetAndGetForm()
	{
		$form = clone $this->_form;
		$handler = $this->_handler->setForm($form);

		$this->_engine->expects($this->once())
			->method('addHelpers');

		$this->assertSame($form, $this->_handler->getForm());
		$this->assertSame($handler, $this->_handler);
	}

	public function testSetAndGetValidator()
	{
		$validator = clone $this->_validator;
		$handler = $this->_handler->setValidator($validator);
		$this->assertSame($validator, $this->_handler->getValidator());
		$this->assertSame($handler, $this->_handler);
	}

	public function testFieldNoName()
	{
		$this->_form->expects($this->once())
			->method('all')
			->will($this->returnValue(
				array(
					'one',
					'two'
				)
			));

		$result = $this->_handler->field();
		$this->assertSame('two', $result);
	}

	public function testFieldWithName()
	{
		$this->_form->expects($this->once())
			->method('all')
			->will($this->returnValue(
				array(
					'yes' => 'one',
					'no' => 'two'
				)
			));

		$result = $this->_handler->field('yes');
		$this->assertSame('one', $result);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testFieldNoChildren()
	{
		$this->_form->expects($this->once())
			->method('all')
			->will($this->returnValue(
				array()
			));

		$this->_handler->field();
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testFieldNoChildrenWithName()
	{
		$this->_form->expects($this->once())
			->method('all')
			->will($this->returnValue(
				array()
			));

		$this->_handler->field('test');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testFieldNotExist()
	{
		$this->_form->expects($this->once())
			->method('all')
			->will($this->returnValue(
				array('no' => 'one')
			));

		$this->_handler->field('yes');
	}

	public function testIsValidBound()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->once())
			->method('getData')
			->will($this->returnValue(array()));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(true));

		$this->assertTrue($this->_handler->isValid());
	}

	public function testIsValidBoundNotValid()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->once())
			->method('getData')
			->will($this->returnValue(array()));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(false));

		$this->assertFalse($this->_handler->isValid());
	}

	public function testIsValidBoundWithData()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->never())
			->method('getData');

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(true));

		$this->assertTrue($this->_handler->isValid(false, array('data')));
	}

	public function testIsValidBoundWithDataNotValid()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->never())
			->method('getData');

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(false));

		$this->assertFalse($this->_handler->isValid(false, array('data')));
	}

	public function testIsValidBoundFromPost()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->never())
			->method('getData');

		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(true));

		$this->assertTrue($this->_handler->isValid(true));
	}

	public function testIsValidBoundFromPostNotValid()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->never())
			->method('getData');

		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(false));

		$this->assertFalse($this->_handler->isValid(true));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testIsValidNotBoundNoDataNoPost()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_handler->isValid();
	}

	public function testIsValidNotBoundWithData()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(true));

		$this->assertTrue($this->_handler->isValid(false, array('data')));
	}

	public function testIsValidNotBoundWithDataNotValid()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(false));

		$this->assertFalse($this->_handler->isValid(false, array('data')));
	}

	public function testIsValidNotBoundFromPost()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_form->expects($this->never())
			->method('getData');

		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(true));

		$this->assertTrue($this->_handler->isValid(true));
	}

	public function testIsValidNotBoundFromPostNotValid()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_form->expects($this->never())
			->method('getData');

		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(false));

		$this->assertFalse($this->_handler->isValid(true));
	}

	public function testGetFilteredData()
	{
		$this->_validator->expects($this->once())
			->method('validate');

		$this->_validator->expects($this->once())
			->method('getData');

		$this->_handler->getFilteredData();
	}

	public function testGetFilteredDataWithData()
	{
		$this->_validator->expects($this->once())
			->method('validate');

		$this->_validator->expects($this->once())
			->method('getData');

		$this->_handler->getFilteredData(array('data'));
	}

	public function testGetDataBound()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->once())
			->method('getData')
			->will($this->returnValue(array('data')));

		$this->assertSame(array('data'), $this->_handler->getData());
	}

	// @todo this currently fails: isBound was expected to be called 1 times, actually called 0 times.
	public function testGetDataNotBound()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_form->expects($this->never())
			->method('getData');

		$this->assertSame(array(), $this->_handler->getData());
	}

	public function testIsPostTrue()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->assertTrue($this->_handler->isPost());
	}

	public function testIsPostFalse()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(null));

		$this->_form->expects($this->once())
			->method('getName');

		$this->assertFalse($this->_handler->isPost());
	}

	public function testGetPost()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->_form->expects($this->once())
			->method('getName');

		$this->assertSame(array('data'), $this->_handler->getPost());
	}

	public function testGetPostNoPost()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(null));

		$this->_form->expects($this->once())
			->method('getName');

		$this->assertSame(array(), $this->_handler->getPost());
	}

	public function testGetMessages()
	{
		$this->_validator->expects($this->once())
			->method('getMessages')
			->will($this->returnValue(array('messages')));

		$this->assertSame(array('messages'), $this->_handler->getMessages());
	}
}