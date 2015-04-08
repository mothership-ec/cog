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
	public function setCache(CacheInterface $cache);
}