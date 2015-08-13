<?php

namespace Message\Cog\Test\Application\Context;

use Message\Cog\Application\Context\Console;
use Message\Cog\Application\Environment;
use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Console\Command\Foo1Command;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
	protected $_container;
	protected $_context;

	public function setUp()
	{
		$_SERVER['argv']  = array('/usr/bin/php', 'foo:bar1');
		$this->_container = new FauxContainer;
	}

	public function testRunRunsConsole()
	{
		$this->setUpContextClass();

		ob_start();
		$this->_context->run();
		ob_end_clean();
	}

	public function getEnvironmentOptions()
	{
		return array(
			array(array('--env=live'), 		'live'),
			array(array('--env=test'), 		'test'),
			array(array('--env=dev'), 		'dev'),
			array(array('--env=staging'), 	'staging'),
			array(array('--env=local'), 	'local'),
		);
	}
}