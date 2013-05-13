<?php

namespace Message\Cog\Test\Cache\Adapter;

use Message\Cog\Cache\CacheInterface;

/**
 * Cache adapter that just uses an internal array - caches are not retained
 * between requests.
 *
 * This simply inherits the TreasureChest FauxCache.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Faux extends \TreasureChest\Cache\Faux implements CacheInterface
{

}