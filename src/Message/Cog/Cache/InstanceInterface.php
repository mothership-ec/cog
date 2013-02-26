<?php

namespace Message\Cog\Cache;

/**
 * Interface for the cache "instance", the wrapper class for the cache engine
 * itself.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface InstanceInterface
{
	public function setPrefix($prefix);
	public function setDelimiter($delimiter);
	public function invalidate($namespace);
}