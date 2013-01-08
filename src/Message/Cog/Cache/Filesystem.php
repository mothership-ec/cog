<?php

namespace Message\Cog\Cache;

use TreasureChest\Cache\Filesystem as TCFilesystem;

/**
*
*/
class Filesystem extends TCFilesystem implements CacheInterface
{
	public function clear()
	{
		return (bool) array_map('unlink', glob($this->path . '*'));
	}
}