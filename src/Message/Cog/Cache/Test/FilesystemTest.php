<?php

namespace Message\Cog\Cache\Test;

use namespace Message\Cog\Cache\Filesystem;


class FilesystemTest extends \PHPUnit_Framework_TestCase
{
	private $_path = '/tmp/cache_unit_test'; // where to store cache files temporarily

	protected function setUp()
	{
		mkdir($this->_path);
		chmod($this->_path), 777);
		$this->cache = new Filesystem($this->_path);
	}

	protected function tearDown()
	{
		// delete all cache files
		array_map('unlink', glob(rtrim($this->_path, '/').'/*'));
		rmdir($this->_path);
	}

	/**
	* @expectedException \TreasureChest\Exception\Cache
	*/
	public function testInvalidPath()
	{
		$cache = new Filesystem('/path/that/should/never/exist?');
	}

	public function testCache()
	{
		$this->assertTrue($cache->store('email', 'bob@example.org'));
		$this->assertTrue($cache->store('age', 45));
		$cache->fetch('email', $result);
		$this->assertTrue($result);
		$this->assertSame($cache->inc('age', 5), 50);
		$this->assertSame($cache->dec('age', 10), 40);
		$this->assertTrue($cache->delete('email'));
		$cache->fetch('email', $result);
		$this->assertFalse($result);
	}
}