<?php

namespace Message\Cog\Test\Application\Context;

use Message\Cog\Application\Context\Console;
use Message\Cog\Environment;
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

	public function setUpContextClass()
	{
		$this->_context = new Console($this->_container);

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

		$_SERVER['argv'][] = $option;

		$this->setUpContextClass();

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
			array('-e live', 'live'),
			array('-elive', 'live'),
			array('--env=live', 'live'),
			array('-e test', 'test'),
			array('-etest', 'test'),
			array('--env=test', 'test'),
			array('-e dev', 'dev'),
			array('-edev', 'dev'),
			array('--env=dev', 'dev'),
			array('-e staging', 'staging'),
			array('-estaging', 'staging'),
			array('--env=staging', 'staging'),
			array('-e local', 'local'),
			array('-elocal', 'local'),
			array('--env=local', 'local'),
		);
	}
}