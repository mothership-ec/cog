<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Test\Event\FauxDispatcher;
use Message\Cog\Form\Form;
use Message\Cog\Form\Data;
use Message\Cog\Service\Container;

class FormTest extends \PHPUnit_Framework_TestCase
{
	protected $_configBuilder;
	protected $_form;

	public function setUp()
	{
		$this->_eventDispatcher = new FauxDispatcher;

		$this->_configBuilder = $this->getMock(
			'\Message\Cog\Form\ConfigBuilder',
			array(),
			array('test', '\Message\Cog\Form\Data', $this->_eventDispatcher));

		$this->_form = new Form($this->_configBuilder);
	}

	public function test()
	{

	}
}