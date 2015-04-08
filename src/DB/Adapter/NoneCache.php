<?php

namespace Message\Cog\DB\Adapter;

/**
 * Class NoCache
 * @package Message\Cog\DB\Adapter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Object representing no caching. Methods have no functionality.
 */
class NoneCache implements CacheInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'none';
	}

	/**
	 * {@inheritDoc}
	 */
	public function resultInCache($query)
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCachedResult($query)
	{
		throw new \LogicException('Cannot load result from NoCache');
	}

	/**
	 * {@inheritDoc}
	 */
	public function cacheResult($query, ResultInterface $result)
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function clear()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function countCached()
	{
		return 0;
	}

	public function countLoadedFromCache()
	{
		return 0;
	}
}