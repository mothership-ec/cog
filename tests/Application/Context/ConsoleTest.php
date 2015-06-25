<?php

namespace Message\Cog\Test\Application\Context;

use Message\Cog\Application\Context\Console;
use Message\Cog\Application\Environment;
use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Console\Command\Foo1Command;
use Mockery as m;

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
		$command = m::mock('Message\Cog\Console\Command');

		$command->shouldReceive('setContainer')->zeroOrMoreTimes();
		$command->shouldReceive('setApplication')->zeroOrMoreTimes();
		$command->shouldReceive('isEnabled')->zeroOrMoreTimes()->andReturn(true);
		$command->shouldReceive('getName')->zeroOrMoreTimes()->andReturn('foo:bar1');
		$command->shouldReceive('getAliases')->zeroOrMoreTimes()->andReturn([]);
		$command->shouldReceive('getSynopsis')->zeroOrMoreTimes()->andReturn('Synopsis');
		$command->shouldReceive('run')->zeroOrMoreTimes()->andReturn(true);

		$this->_container['console.app']->setAutoExit(false);
		$this->_container['console.app']->add($command);
	}

	public function testConsoleServiceDefined()
	{
		$this->setUpContextClass();

		$this->assertTrue($this->_container->isShared('console.app'));
		$this->assertInstanceOf('Symfony\Component\Console\Application', $this->_container['console.app']);
	}

	/**
	 * @dataProvider getEnvironmentOptions
	 */
	public function testEnvironmentOptionSetsCorrectly($option, $expectedEnvironment)
	{
		$environment = new Environment;
		$this->_container['environment'] = $this->_container->factory(function() use ($environment) {
			return $environment;
		});
		$this->_container['env'] = $this->_container->factory(function($c) {
			return $c['environment']->get();
		});

		$this->setUpContextClass(array_merge(array('/usr/bin/php', 'foo:bar1'), $option));

		$this->assertEquals($expectedEnvironment, $this->_container['environment']->get());
		$this->assertEquals($expectedEnvironment, $this->_container['env']);
	}

	public function testConsoleNameGetsSet()
	{
		$this->setUpContextClass();

		ob_start();
		$this->_context->run();
		ob_end_clean();

		$name = $this->_container['console.app']->getName();

		$this->assertInternalType('string', $name);
		$this->assertFalse(empty($name), 'Console name is not empty');
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