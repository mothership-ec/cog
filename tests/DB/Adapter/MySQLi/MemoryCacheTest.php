<?php

namespace Message\Cog\Test\DB\Adapter\MySQLi;

use Message\Cog\DB\Adapter\MySQLi\MemoryCache;

class MemoryCacheTest extends \PHPUnit_Framework_TestCase
{
	private $_cache;

	private $_result;

	const SELECT     = 'SELECT * FROM test';
	const SELECT_2   = 'SELECT this FROM test';
	const NON_SELECT = 'DROP test';

	protected function setUp()
	{
		$this->_cache = new MemoryCache;
		$this->_result = $this->getMockBuilder('Message\\Cog\\DB\\Adapter\\MySQLi\\Result')->disableOriginalConstructor()->getMock();
	}

	public function testGetName()
	{
		$this->assertSame('mysql_memory', $this->_cache->getName());
	}

	public function testResultInCacheTrue()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertTrue($this->_cache->resultInCache(self::SELECT));
	}

	public function testResultInCacheFalse()
	{
		$this->assertFalse($this->_cache->resultInCache(self::SELECT));
	}

	public function testGetCachedResult()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame($this->_result, $this->_cache->getCachedResult(self::SELECT));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testGetCachedResultException()
	{
		$this->_cache->getCachedResult(self::SELECT);
	}

	public function testGetCachedResultMultipleQueries()
	{
		$res1 = clone $this->_result;
		$res2 = clone $this->_result;

		$this->_cache->cacheResult(self::SELECT, $res1);
		$this->_cache->cacheResult(self::SELECT_2, $res2);

		$this->assertSame($res1, $this->_cache->getCachedResult(self::SELECT));
		$this->assertSame($res2, $this->_cache->getCachedResult(self::SELECT_2));
	}

	public function testClear()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame(1, $this->_cache->countCached());
		$this->_cache->clear();
		$this->assertSame(0, $this->_cache->countCached());
	}

	public function testClearAfterNonSelect()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame(1, $this->_cache->countCached());
		$this->_cache->cacheResult(self::NON_SELECT, $this->_result);
		$this->assertSame(0, $this->_cache->countCached());
	}

	public function testIncrementCount()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame(1, $this->_cache->countCached());
		$this->_cache->cacheResult(self::SELECT_2, $this->_result);
		$this->assertSame(2, $this->_cache->countCached());
	}

	public function testQueriesAreTrimmedOnRetrieval()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame($this->_result, $this->_cache->getCachedResult('   ' . PHP_EOL . self::SELECT . PHP_EOL . '   '));
	}

	public function testQueriesAreTrimmedOnCache()
	{
		$this->_cache->cacheResult('    ' . PHP_EOL . self::SELECT . '   ' . PHP_EOL, $this->_result);
		$this->assertSame($this->_result, $this->_cache->getCachedResult(self::SELECT));
	}

	public function testCountLoadedFromCache()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->_cache->getCachedResult(self::SELECT);
		$this->assertSame(1, $this->_cache->countLoadedFromCache());
		$this->_cache->getCachedResult(self::SELECT);
		$this->assertSame(2, $this->_cache->countLoadedFromCache());
	}

	public function testCountLoadedFromCacheAfterClear()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->_cache->getCachedResult(self::SELECT);
		$this->assertSame(1, $this->_cache->countLoadedFromCache());
		$this->_cache->clear();
		$this->assertSame(1, $this->_cache->countLoadedFromCache());
	}
}