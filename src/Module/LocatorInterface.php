<?php

namespace Message\Cog\Module;

/**
 * Interface for the module locator. The module locator handles locating module
 * directories within the file system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LocatorInterface
{
	public function getPath($moduleName);
}