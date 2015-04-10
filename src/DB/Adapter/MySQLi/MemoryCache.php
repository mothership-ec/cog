<?php

namespace Message\Cog\DB\Adapter\MySQLi;

use Message\Cog\DB\Adapter\CacheInterface;
use Message\Cog\DB\Adapter\ResultInterface;

/**
 * Class MemoryCache
 * @package Message\Cog\DB\Adapter\MySQLi
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class MemoryCache implements CacheInterface
{
	/**
	 * Cached items with hashed queries for keys
	 *
	 * @var array
	 */
	private $_cache = [];

	/**
	 * Array of hashed queries that have already been confirmed to be selects
	 *
	 * @var array
	 */
	private $_selects = [];

	/**
	 * Number of query results loaded from the cache
	 *
	 * @var int
	 */
	private $_loadedFromCache = 0;

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'mysql_memory';
	}

	/**
	 * {@inheritDoc}
	 */
	public function resultInCache($query)
	{
		$query = $this->_parseQuery($query);

		return array_key_exists($this->_getCacheKey($query), $this->_cache);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCachedResult($query)
	{
		$query = $this->_parseQuery($query);

		if (!$this->resultInCache($query)) {
			throw new \LogicException('Attempting to get cached result that does not exist');
		}

		$this->_loadedFromCache++;

		return $this->_cache[$this->_getCacheKey($query)];
	}

	/**
	 * {@inheritDoc}
	 */
	public function cacheResult($query, ResultInterface $result)
	{
		$query = $this->_parseQuery($query);

		if (!$this->resultInCache($query) && $this->_isSelect($query)) {
			$this->_cache[$this->_getCacheKey($query)] = $result;
		} elseif (!$this->_isSelect($query)) {
			$this->clear();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear()
	{
		$this->_cache = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function countCached()
	{
		return count($this->_cache);
	}

	/**
	 * {@inheritDoc}
	 */
	public function countLoadedFromCache()
	{
		return $this->_loadedFromCache;
	}

	/**
	 * Trim white space off the query
	 *
	 * @param $query
	 *
	 * @return string
	 */
	private function _parseQuery($query)
	{
		return trim($query);
	}

	/**
	 * Check to see if the query is a select query
	 *
	 * @return bool
	 */
	private function _isSelect($query)
	{
		$key = $this->_getCacheKey($query);
		if (in_array($key, $this->_selects)) {
			return true;
		}

		if (preg_match('/^select*/i', $query)) {
			$this->_selects[] = $key;
		}

		return in_array($key, $this->_selects);
	}

	/**
	 * Trim and hash the parsed query to create a key for the cached query
	 *
	 * @return string
	 */
	private function _getCacheKey($query)
	{
		return md5($query);
	}
}