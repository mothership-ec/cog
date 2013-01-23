<?php

namespace Message\Cog\Config;

use Message\Cog\Application\Environment;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Cache\CacheInterface;

/**
 * Configuration cache loader.
 *
 * Responsible for loading configurations from the cache, and saving compiled
 * configurations to the cache for speedy retrieval later.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class LoaderCache extends Loader
{
	const CACHE_KEY_PATTERN = 'cache.%s';

	protected $_cache;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $services The service container to add configs to
	 * @param Environment        $env      The environment object
	 * @param CacheInterface     $cache    The caching engine to use
	 */
	public function __construct(ContainerInterface $services, Environment $env, CacheInterface $cache)
	{
		parent::__construct($services, $env);

		$this->_cache = $cache;
	}

	public function loadFromDirectory($dir)
	{
		// todo: below line is duplicated... make it not!
		$dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$cacheKey = sprintf(self::CACHE_KEY_PATTERN, md5($dir));

		if ($this->loadFromCache($cacheKey)) {
			return true;
		}

		$return = parent::loadFromDirectory($dir);

		$this->_cache->store($cacheKey, $return);

		return $return;
	}

	public function loadFromCache($key)
	{
		if (!$this->_cache->exists($key)) {
			return false;
		}

		$result = $this->_cache->fetch($key);

		try {
			if (!is_array($result)) {
				throw new Exception(sprintf('Config cache `%s` is not an array', $key));
			}

			foreach ($result as $name => $group) {
				if (!$name) {
					throw new Exception(sprintf('Config cache `%s` has empty group key(s)', $key));
				}
				if (!$group instanceof Group) {
					throw new Exception(sprintf('Config cache `%s` group `%s` value was not a valid Group instance', $key, $name));
				}

				$this->_addService($name, $group);
			}
		}
		catch (Exception $e) {
			$this->_cache->delete($key);

			return false;
		}

		return true;
	}
}