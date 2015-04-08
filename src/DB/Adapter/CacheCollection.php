<?php

namespace Message\Cog\DB\Adapter;

use Message\Cog\ValueObject\Collection;

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