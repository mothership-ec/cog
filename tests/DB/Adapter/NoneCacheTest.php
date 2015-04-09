<?php

use Message\Cog\DB\Adapter\NoneCache;

class NoneCacheTest extends \PHPUnit_Framework_TestCase
{
	private $_cache;

	private $_result;

	const SELECT     = 'SELECT * FROM test';
	const SELECT_2   = 'SELECT this FROM test';
	const NON_SELECT = 'DROP test';

	protected function setUp()
	{
		$this->_cache = new NoneCache;
		$this->_result = $this->getMockBuilder('Message\\Cog\\DB\\Adapter\\MySQLi\\Result')->disableOriginalConstructor()->getMock();
	}

	public function testGetName()
	{
		$this->assertSame('none', $this->_cache->getName());
	}

	public function testResultInCacheNoCache()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertFalse($this->_cache->resultInCache(self::SELECT));
	}

	public function testResultInCacheNoCachingAttempt()
	{
		$this->assertFalse($this->_cache->resultInCache(self::SELECT));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testGetCachedResultThrowExceptionAfterCache()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame($this->_result, $this->_cache->getCachedResult(self::SELECT));
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testGetCachedResultThrowExceptionNoCache()
	{
		$this->_cache->getCachedResult(self::SELECT);
	}

	public function testClear()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->_cache->clear();
		$this->assertSame(0, $this->_cache->countCached());
	}

	public function testNoCount()
	{
		$this->_cache->cacheResult(self::SELECT, $this->_result);
		$this->assertSame(0, $this->_cache->countCached());
		$this->_cache->cacheResult(self::SELECT_2, $this->_result);
		$this->assertSame(0, $this->_cache->countCached());
	}

	public function testCountLoadedFromCache()
	{
		$this->assertSame(0, $this->_cache->countLoadedFromCache());
	}
}