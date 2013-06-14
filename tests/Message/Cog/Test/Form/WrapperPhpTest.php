<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Form\Creator;

/**
 * Class CreatorPhpTest
 * @package Message\Cog\Test\Form
 */

class CreatorPhpTest extends \PHPUnit_Framework_TestCase
{
	protected $_creator;

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

		$container['templating.php.engine'] = $this->_engine;

		// Mock helpers
		$this->_helper = $this->getMockBuilder('\\Message\\Cog\\Form\\Template\\Helper')
			->disableOriginalConstructor()
			->getMock();

		$container['form.helper.php'] = $this->_helper;

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

		$this->_creator = new Creator($container, 'php');
		$this->_creator->setForm($this->_form);
	}

	public function testClear()
	{
		$this->_builder->expects($this->once())
			->method('getForm')
			->will($this->returnValue($this->_form));

		$this->_creator->clear();

		$this->assertEquals($this->_form, $this->_creator->getForm());
		$this->assertEquals($this->_validator, $this->_creator->getValidator());
	}

	public function testAddStringChildOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add('test');
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddObjectChildOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add($this->_form);
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddStringWithType()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add('test', 'select');
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddObjectWithType()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add($this->_form, 'select');
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddStringWithTypeOptions()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add('test', 'select', array('option' => true));
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddObjectWithTypeOptions()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add($this->_form, 'select', array('option' => true));
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddStringWithOptionsOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add('test', null, array('option' => true));
		$this->assertSame($creator, $this->_creator);
	}

	public function testAddObjectWithOptionsOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$creator = $this->_creator->add($this->_form, null, array('option' => true));
		$this->assertSame($creator, $this->_creator);
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

		$this->_creator->add(null);
	}

	public function testVal()
	{
		$this->assertInstanceOf('\\Message\\Cog\\Validation\\Validator', $this->_creator->val());
	}

	public function testSetAndGetForm()
	{
		$form = clone $this->_form;
		$creator = $this->_creator->setForm($form);

		$this->_engine->expects($this->once())
			->method('addHelpers');

		$this->assertSame($form, $this->_creator->getForm());
		$this->assertSame($creator, $this->_creator);
	}

	public function testSetAndGetValidator()
	{
		$validator = clone $this->_validator;
		$creator = $this->_creator->setValidator($validator);
		$this->assertSame($validator, $this->_creator->getValidator());
		$this->assertSame($creator, $this->_creator);
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

		$result = $this->_creator->field();
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

		$result = $this->_creator->field('yes');
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

		$this->_creator->field();
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

		$this->_creator->field('test');
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

		$this->_creator->field('yes');
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

		$this->assertTrue($this->_creator->isValid());
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

		$this->assertFalse($this->_creator->isValid());
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

		$this->assertTrue($this->_creator->isValid(false, array('data')));
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

		$this->assertFalse($this->_creator->isValid(false, array('data')));
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

		$this->assertTrue($this->_creator->isValid(true));
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

		$this->assertFalse($this->_creator->isValid(true));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testIsValidNotBoundNoDataNoPost()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_creator->isValid();
	}

	public function testIsValidNotBoundWithData()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(true));

		$this->assertTrue($this->_creator->isValid(false, array('data')));
	}

	public function testIsValidNotBoundWithDataNotValid()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_validator->expects($this->once())
			->method('validate')
			->will($this->returnValue(false));

		$this->assertFalse($this->_creator->isValid(false, array('data')));
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

		$this->assertTrue($this->_creator->isValid(true));
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

		$this->assertFalse($this->_creator->isValid(true));
	}

	public function testGetFilteredData()
	{
		$this->_validator->expects($this->once())
			->method('validate');

		$this->_validator->expects($this->once())
			->method('getData');

		$this->_creator->getFilteredData();
	}

	public function testGetFilteredDataWithData()
	{
		$this->_validator->expects($this->once())
			->method('validate');

		$this->_validator->expects($this->once())
			->method('getData');

		$this->_creator->getFilteredData(array('data'));
	}

	public function testGetDataBound()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(true));

		$this->_form->expects($this->once())
			->method('getData')
			->will($this->returnValue(array('data')));

		$this->assertSame(array('data'), $this->_creator->getData());
	}

	// @todo this currently fails: isBound was expected to be called 1 times, actually called 0 times.
	public function testGetDataNotBound()
	{
		$this->_form->expects($this->once())
			->method('isBound')
			->will($this->returnValue(false));

		$this->_form->expects($this->never())
			->method('getData');

		$this->assertSame(array(), $this->_creator->getData());
	}

	public function testIsPostTrue()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->assertTrue($this->_creator->isPost());
	}

	public function testIsPostFalse()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(null));

		$this->_form->expects($this->once())
			->method('getName');

		$this->assertFalse($this->_creator->isPost());
	}

	public function testGetPost()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(array('data')));

		$this->_form->expects($this->once())
			->method('getName');

		$this->assertSame(array('data'), $this->_creator->getPost());
	}

	public function testGetPostNoPost()
	{
		$this->_request->expects($this->once())
			->method('get')
			->will($this->returnValue(null));

		$this->_form->expects($this->once())
			->method('getName');

		$this->assertSame(array(), $this->_creator->getPost());
	}

	public function testGetMessages()
	{
		$this->_validator->expects($this->once())
			->method('getMessages')
			->will($this->returnValue(array('messages')));

		$this->assertSame(array('messages'), $this->_creator->getMessages());
	}
}