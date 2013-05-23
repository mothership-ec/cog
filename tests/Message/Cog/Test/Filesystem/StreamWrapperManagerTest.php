<?php

namespace Message\Cog\Test\Filesystem;

use Message\Cog\Filesystem\StreamWrapperManager;

class StreamWrapperManagerTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->manager = new StreamWrapperManager();
	}

	public function testClearing()
	{
		$this->manager->register('test-one',   function(){});
		$this->manager->register('test-two',   function(){});
		$this->manager->register('test-three', function(){});

		$this->assertSame(3, count($this->manager->getHandlers()));

		$this->manager->clear();

		$this->assertSame(0, count($this->manager->getHandlers()));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testStreamIsRegistered()
	{
		$this->manager->register('test', function(){});
		$this->manager->register('test', function(){});
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 */
	public function testBadStreamName()
	{
		$this->manager->register('tes{}S+4:://t', function(){});
	}

	/**
	 * @expectedException \Exception
	 */
	public function testAlreadyRegisteredStream()
	{
		$this->manager->register('test', function(){});
		$this->manager->register('test', function(){});
	}


}