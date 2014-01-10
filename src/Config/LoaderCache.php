<?php

namespace Message\Cog\Config;

use Message\Cog\Application\EnvironmentInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Cache\InstanceInterface;

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
	const CACHE_KEY_PATTERN = 'config:%s';

	protected $_cache;
	protected $_cacheKey;

	/**
	 * Constructor.
	 *
	 * @param string               $dir   Directory to load configs from
	 * @param EnvironmentInterface $env   The environment object
	 * @param InstanceInterface    $cache The cache instance
	 */
	public function __construct($dir, EnvironmentInterface $env, InstanceInterface $cache)
	{
		parent::__construct($dir, $env);

		$this->_cache    = $cache;
		$this->_cacheKey = sprintf(self::CACHE_KEY_PATTERN, md5($this->_dir));
	}

	/**
	 * Get the cache key for the directory being loaded from.
	 *
	 * @return string The cache key
	 */
	public function getCacheKey()
	{
		return $this->_cacheKey;
	}

	/**
	 * Load the configuration files.
	 *
	 * If `loadFromCache` doesn't return false (meaning the configuration groups
	 * were loaded from the cache), then the registry is returned straight away
	 * and the filesystem loading is bypassed.
	 *
	 * If the configuration groups could not be loaded from the cache, then
	 * `load()` on the parent class is called, loading and compiling the
	 * configuration groups from the filesystem. They are then cached for speedy
	 * retrieval next time.
	 *
	 * @see loadByCache
	 * @see parent::load()
	 *
	 * @param  Registry $registry The configuration registry to add compiled
	 *                            configurations to
	 * @return Registry           The same registry is returned
	 */
	public function load(Registry $registry)
	{
		if ($this->loadFromCache($registry)) {
			return $registry;
		}

		$return = parent::load($registry);

		$this->_cache->store($this->_cacheKey, $registry->getAll());

		return $return;
	}

	/**
	 * Try to load the configuration groups from the cache.
	 *
	 * If the configuration groups are found in the cache, but they are invalid
	 * in any way, then they are cleared from the cache.
	 *
	 * @param  Registry $registry The configuration registry to add configurations to
	 * @return boolean            True if the configurations could be loaded
	 *                            from the cache
	 */
	public function loadFromCache(Registry $registry)
	{
		if (!$this->_cache->exists($this->getCacheKey())) {
			return false;
		}

		$result = $this->_cache->fetch($this->getCacheKey());

		try {
			if (!is_array($result)) {
				throw new Exception(sprintf('Config cache `%s` is not an array', $this->getCacheKey()));
			}

			foreach ($result as $name => $group) {
				if (!$name) {
					throw new Exception(sprintf('Config cache `%s` has empty group key(s)', $this->getCacheKey()));
				}
				if (!($group instanceof Group)) {
					throw new Exception(sprintf('Config cache `%s` group `%s` value was not a valid Group instance', $this->getCacheKey(), $name));
				}

				$registry->$name = $group;
			}
		}
		catch (Exception $e) {
			$this->_cache->delete($this->getCacheKey());

			return false;
		}

		return true;
	}
}