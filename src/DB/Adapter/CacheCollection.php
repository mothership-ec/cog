<?php

namespace Message\Cog\DB\Adapter;

use Message\Cog\ValueObject\Collection;

/**
 * Class CacheCollection
 * @package Message\Cog\DB\Adapter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Collection of caches. The caching method can be declared in the db.yml config.
 */
class CacheCollection extends Collection
{
	protected function _configure()
	{
		$this->addValidator(function ($item) {
			if (!$item instanceof CacheInterface) {
				throw new \InvalidArgumentException('Cache must implement CacheInterface');
			}
		});

		$this->setKey(function ($item) {
			return $item->getName();
		});
	}
}