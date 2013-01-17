<?php

namespace Message\Cog\Config;

class Loader
{
	protected $_services;
	protected $_cache;
	protected $_env;

	public function __construct(ContainerInterface $services, CacheInterface $cache, Environment $env)
	{
		$this->_services = $services;
		$this->_cache    = $cache;
		$this->_env      = $env;
	}

	public function loadFromDirectory($dir)
	{
		if (!file_exists($dir)) {
			throw new Exception(sprintf('Config directory `%s` does not exist', $dir));
		}

		if (!is_readable($dir)) {
			throw new Exception(sprintf('Config directory `%s` is not readable'));
		}

		try {
			return $this->loadFromCache('cache.' . md5($dir));
		}
		catch (Exception $e) {
			// load from directory
		}

		if ($this->_cache->exists($cacheKey)) {
			$this->loadFromCache($cacheKey);
		}

		# CACHE KEY: config.md5($directory)

		// get overwrite files for current environment

		// get overwrite files for current env&installation

		// loop through files in root directory
			// grab overwrite for current env if exists
			// grab overwrite for current env&installation if exists
			//
			// for each yaml file, look for env folder version of the config
			// and also an installation folder
		$compiler = new Compiler('merchant'); // second argument to not cache?
		$compiler->add($yamlDataFromBase);
		$compiler->add($yamlDataFromEnvDir);
		$compiler->add($yamlDataFromEnvInstallationDir);

		$group = $compiler->compile();
	}

	public function loadFromCache($key)
	{
		if (!$this->_cache->exists($key)) {
			throw new Exception(sprintf('Config cache `%s` does not exist', $key));
		}

		$result = $this->_cache->fetch($key);

		try {
			if (!is_array($result)) {
				throw new Exception\CacheInvalidException(sprintf('Config cache `%s` is not an array', $key));
			}

			foreach ($result as $name => $group) {
				if (!$name) {
					throw new Exception\CacheInvalidException(sprintf('Config cache `%s` has empty group key(s)', $key));
				}
				if (!$group instanceof Group) {
					throw new Exception\CacheInvalidException(sprintf('Config cache `%s` group `%s` value was not a valid Group instance', $key, $name));
				}

				$this->_addService($name, $group);
			}
		}
		catch (Exception\CacheInvalidException $e) {
			$this->_cache->delete($key);

			throw new Exception\CacheInvalidException(sprintf('Config cache `%s` is invalid', $key), null, $e);
		}
		// get 'em, add them all to the service container
	}

	protected function _addService($name, Group $config)
	{
		$this->_services['cfg.' . $name] = $this->_services->share(function() use ($config) {
			return $config;
		});
	}
}