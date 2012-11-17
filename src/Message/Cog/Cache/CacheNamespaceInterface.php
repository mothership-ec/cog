<?php

namespace Message\Cog\Cache;

interface CacheNamespaceInterface
{
	public function invalidate($namespace);
}