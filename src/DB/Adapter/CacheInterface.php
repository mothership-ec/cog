<?php

namespace Message\Cog\DB\Adapter;

/**
 * Interface CacheInterface
 * @package Message\Cog\DB\Adapter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Interface representing a cache of database results. When implementing this class, you must be careful that you
 * consider the fact that changes may happen to the database within a single request that could change the result of
 * a query.
 */
interface CacheInterface
{
	/**
	 * Get the name of this cache
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Check to see if query has been run before and the result exists in memory
	 *
	 * @param string $query   The query that a result could be assigned to
	 *
	 * @return bool
	 */
	public function resultInCache($query);

	/**
	 * Get the result from the cache using the parsed query as the key
	 *
	 * @param string $query      The query that the result is assigned to
	 * @throws \LogicException   Throws exception if query result has not been cached
	 *
	 * @return ResultInterface
	 */
	public function getCachedResult($query);

	/**
	 * Store the result in the cache with the parsed query as the key
	 *
	 * @param string $query             The query that the returned the result
	 * @param ResultInterface $result   The result of the query
	 */
	public function cacheResult($query, ResultInterface $result);

	/**
	 * Delete the cache
	 */
	public function clear();

	/**
	 * Get the number of items in the cache
	 *
	 * @return int
	 */
	public function countCached();

	/**
	 * Count the number of results that have been loaded from the cache
	 *
	 * @return int
	 */
	public function countLoadedFromCache();
}