<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\Registry;

class NonLoadingRegistry extends Registry
{
	/**
	 * Disable calling the configuration loader. This is helpful when testing
	 * the loader itself using the registry.
	 *
	 * @return false
	 */
	public function load()
	{
		return false;
	}
}