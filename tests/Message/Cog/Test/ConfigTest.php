<?php

namespace Message\Cog\Test;

use Message\Cog\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_path   = __DIR__ . '/Config/fixtures';
		$this->_env    = 'local';
		$this->_config = new Config($this->_path, $this->_env);
	}

	public function testConfigsLoaded()
	{
		$files = new \DirectoryIterator($this->_path);
		foreach($files as $file){
			if ($file->getExtension() === 'yml') {
				$name = $file->getBasename('.yml');
				$this->assertTrue(isset($this->_config->$name));
			}
		}
	}

	public function testConfigsGetConvertedToObjects()
	{
		$this->assertInternalType('object', $this->_config->example);
	}

	public function testConfigsOverride()
	{
		$this->_config = new Config($this->_path, 'live');

		// Simple example
		$this->assertEquals('live.message.uk.com', $this->_config->example->url);

		// Nested
		$this->assertEquals('Atlas Chambers', $this->_config->example->address->line1);
		$this->assertEquals('Hove', $this->_config->example->address->town);
		$this->assertEquals('message_live', $this->_config->example->gateway->sagepay->vendor);
	}

	public function testKeysAreConvertedToCamelCase()
	{
		$this->assertEquals('0123456789', $this->_config->example->vatRegistrationNumber);
	}

	public function testArraysDontGetConvertedToObjects()
	{
		$this->assertInternalType('array', $this->_config->products);
		$this->assertInternalType('array', $this->_config->example->admins);
	}

	public function testArraysOverride()
	{
		$this->_config = new Config($this->_path, 'live');
		$this->assertEquals(array('Mark Bobkins', 'Bob Smith'), $this->_config->example->admins);
	}

	public function testEmptyConfig()
	{
		$this->assertTrue(isset($this->_config->empty));
	}

	public function testIssetingMissingConfig()
	{
		$this->assertFalse(isset($this->_config->thisdoesnotexist));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAccessingMissingConfigThrowsException()
	{
		$this->_config->thisdoesnotexist;
	}
}