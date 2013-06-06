<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Form\Wrapper;

class WrapperTest extends \PHPUnit_Framework_TestCase
{
	protected $_phpWrapper;

	public function setUp()
	{
		$container = $this->getMock('\\Message\\Cog\\Service\\Container');
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

		$container['form.builder.php'] = $this->getMock(
			'\\Symfony\\Component\\Form\\FormBuilder',
			array(),
			array(
				'form',
				'\\Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\PropertyPathMapper',
				$dispatcher,
				$factory
			));

		$container['form.builder.twig'] = $this->getMock('\\Symfony\\Component\\Form\\FormBuilder');

		$container['validator'] = $this->getMock('\\Message\\Cog\\Validation\\Validator');
		$this->_phpWrapper = new Wrapper($container, 'php');
	}

	public function test()
	{

	}
}