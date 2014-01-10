<?php

namespace Message\Cog\Test\Console;

use Message\Cog\Test\Service\FauxContainer;

use Message\Cog\Console\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
	public function testAddingTask()
	{
		$app = Factory::create(new FauxContainer);

		$this->assertInstanceOf('\\Message\\Cog\\Console\\Application', $app);
	}
}