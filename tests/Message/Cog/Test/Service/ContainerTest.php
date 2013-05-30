<?php

namespace Message\Cog\Test\Service;

use Message\Cog\Service\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
	public function testStaticSingletonAccessor()
	{
		$firstInstance = Container::instance();
		$this->assertInstanceOf('Message\Cog\Service\Container', $firstInstance);
		$this->assertEquals($firstInstance, Container::instance());
	}

	public function testStaticGetAccessor()
	{
		$container = Container::instance();

		$container['test'] = function() {
			return 'hello';
		};

		$this->assertEquals($container['test'], Container::get('test'));
		$this->assertEquals('hello', Container::get('test'));
	}

	public function testGetAll()
	{
		$container = new Container;
		$services  = array(
			'test1' => 'test 1',
			'test2' => new \DateTime,
			'test3' => $this,
			'test4' => null,
		);

		foreach ($services as $key => $definition) {
			$container[$key] = function() use ($definition) {
				return $definition;
			};
		}

		$this->assertEquals($services, $container->getAll());
	}
}