<?php

namespace Message\Cog\Config;

use Message\Cog\Application\Environment;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Cache\CacheInterface;

use DirectoryIterator;

/**
 * Configuration loader.
 *
 * Responsible for loading configuration files and adding the compiled result
 * to the service container.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_services;
	protected $_env;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $services The service container to add configs to
	 * @param Environment        $env      The environment object
	 */
	public function __construct(ContainerInterface $services, Environment $env)
	{
		$this->_services = $services;
		$this->_env      = $env;
	}

	public function loadFromDirectory($dir)
	{
		$dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$configs = array();
		$dirs    = array(
			$dir,
			$dir . $this->_env->get() . '/',
			$dir . $this->_env->get() . '/' . $this->_env->installation() . '/',
		);

		foreach ($dirs as $key => $dir) {
			if (!file_exists($dir)) {
				// Only throw exception if base config directory does not exist
				if (0 === $key) {
					throw new Exception(sprintf('Config directory `%s` does not exist', $dir));
				}
				continue;
			}

			if (!is_readable($dir)) {
				throw new Exception(sprintf('Config directory `%s` is not readable', $dir));
			}

			$dir = new DirectoryIterator($dir);

			foreach ($dir as $file) {
				if ('yml' !== $file->getExtension()) {
					continue;
				}

				if (!$file->isReadable()) {
					throw new Exception(sprintf('Config file `%s` is not readable', $file->getPathname()));
				}

				$name = 'cfg.' . $file->getBasename('.yml');

				if (!isset($configs[$name])) {
					$configs[$name] = new Compiler;
				}

				$configs[$name]->add(file_get_contents($file->getPathname()));
			}
		}

		foreach ($configs as $name => $config) {
			$this->_addService($name, $config->compile());
		}

		return true;
	}

	protected function _addService($name, Group $config)
	{
		$this->_services['cfg.' . $name] = $this->_services->share(function() use ($config) {
			return $config;
		});
	}
}