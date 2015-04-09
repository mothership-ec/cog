<?php

namespace Message\Cog\DB\Adapter;

/**
 * Interface CachableInterface
 * @package Message\Cog\DB\Adapter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
interface CachableInterface
{
	/**
	 * Set a result caching object
	 *
	 * @param CacheInterface $cache
	 */
	public function setCache(CacheInterface $cache);

	/**
	 * Disable caching on this object
	 */
	public function disableCache();

	/**
	 * Enable caching on this object
	 */
	public function enableCache();
}