<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Form\Wrapper;

class WrapperTest extends \PHPUnit_Framework_TestCase
{
	protected $_phpWrapper;

	protected $_container;

	protected $_form;

	protected $_validator;

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
		$container['form.builder.php'] = $this->getMock(
			'\\Symfony\\Component\\Form\\FormBuilder',
			array(),
			array(
				'form',
				'\\Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\PropertyPathMapper',
				$dispatcher,
				$factory
			));

		// Add validator mock to container
		$messages = $this->getMock('\\Message\\Cog\\Validation\\Messages');
		$loader = $this->getMock('\\Message\\Cog\\Validation\\Loader', array(), array($messages));
		$this->_validator = $this->getMock(
			'\\Message\\Cog\\Validation\\Validator',
			array(
				'isValid',
				'field',
			),
			array($loader));

		$container['validator'] = $this->_validator;

		$this->_container = $container;

		$this->_form = $this->getMockBuilder('\\Symfony\\Component\\Form\\Form')
			->disableOriginalConstructor()
			->getMock();

		$this->_phpWrapper = new Wrapper($container, 'php');
		$this->_phpWrapper->setForm($this->_form);
	}

	public function testClear()
	{
		$this->_phpWrapper->clear();
	}

	public function testAddStringChildOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add('test');
	}

	public function testAddObjectChildOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add($this->_form);
	}

	public function testAddStringWithType()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add('test', 'select');
	}

	public function testAddObjectWithType()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add($this->_form, 'select');
	}

	public function testAddStringWithTypeOptions()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add('test', 'select', array('option' => true));
	}

	public function testAddObjectWithTypeOptions()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add($this->_form, 'select', array('option' => true));
	}

	public function testAddStringWithOptionsOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add('test', null, array('option' => true));
	}

	public function testAddObjectWithOptionsOnly()
	{
		$this->_form->expects($this->once())
			->method('add');

		$this->_validator->expects($this->once())
			->method('field');

		$this->_phpWrapper->add($this->_form, null, array('option' => true));
	}

	public function testVal()
	{
		$this->assertInstanceOf('\\Message\\Cog\\Validation\\Validator', $this->_phpWrapper->val());
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

		$result = $this->_phpWrapper->field();
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

		$result = $this->_phpWrapper->field('yes');
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

		$this->_phpWrapper->field();
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

		$this->_phpWrapper->field('test');
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

		$this->_phpWrapper->field('yes');
	}
}