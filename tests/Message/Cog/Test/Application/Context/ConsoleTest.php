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

	public function setUpContextClass($args = null)
	{
		//var_dump(__CLASS__, $_SERVER['argv']);
		$this->_context = new Console($this->_container, $args);

		$this->_container['app.console']->setAutoExit(false);
		$this->_container['app.console']->add(new Foo1Command);
	}

	public function testConsoleServiceDefined()
	{
		$this->setUpContextClass();

		$this->assertTrue($this->_container->isShared('app.console'));
		$this->assertInstanceOf('Symfony\Component\Console\Application', $this->_container['app.console']);
	}

	/**
	 * @dataProvider getEnvironmentOptions
	 */
	public function testEnvironmentOptionSetsCorrectly($option, $expectedEnvironment)
	{
		$environment = new Environment;
		$this->_container['environment'] = function() use ($environment) {
			return $environment;
		};
		$this->_container['env'] = function($c) {
			return $c['environment']->get();
		};

		$this->setUpContextClass(array_merge(array('/usr/bin/php', 'foo:bar1'), $option));

		$this->assertEquals($expectedEnvironment, $this->_container['environment']->get());
		$this->assertEquals($expectedEnvironment, $this->_container['env']);
	}

	public function testConsoleNameGetsSet()
	{
		$this->setUpContextClass();

		$console = $this->getMock(
			'Symfony\Component\Console\Application',
			array('run')
		);

		$console
			->expects($this->once())
			->method('run');

		$this->_container['app.console'] = $this->_container->share(function() use ($console) {
			return $console;
		});

		ob_start();
		$this->_context->run();
		ob_end_clean();

		$name = $this->_container['app.console']->getName();

		$this->assertInternalType('string', $name);
		$this->assertFalse(empty($name), 'Console name is not empty');
	}

	public function testRunRunsConsole()
	{
		$this->setUpContextClass();

		ob_start();
		$this->_context->run();
		ob_end_clean();
	}

	// public function testRunOutputsSomething()
	// {
	// 	$dispatcher = $this->getMock(
	// 		'Message\Cog\HTTP\Dispatcher',
	// 		array('handle', 'send'),
	// 		array(),
	// 		'',
	// 		false
	// 	);

	// 	$dispatcher
	// 		->expects($this->once())
	// 		->method('handle')
	// 		->with($this->equalTo($this->_container['http.request.master']))
	// 		->will($this->returnValue($dispatcher));

	// 	$dispatcher
	// 		->expects($this->once())
	// 		->method('send');

	// 	$this->_container['http.dispatcher'] = function() use ($dispatcher) {
	// 		return $dispatcher;
	// 	};

	// 	$this->_webContext->run();
	// }

	public function getEnvironmentOptions()
	{
		return array(
			array(array('-e', 'live'), 		'live'),
			array(array('--env=live'), 		'live'),
			array(array('-e', 'test'), 		'test'),
			array(array('--env=test'), 		'test'),
			array(array('-e', 'dev'), 		'dev'),
			array(array('--env=dev'), 		'dev'),
			array(array('-e', 'staging'), 	'staging'),
			array(array('--env=staging'), 	'staging'),
			array(array('-e', 'local'), 	'local'),
			array(array('--env=local'), 	'local'),
		);
	}
}