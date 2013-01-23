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
class Loader implements LoaderInterface
{
	protected $_dir;

	protected $_services;
	protected $_env;

	/**
	 * Constructor.
	 *
	 * @param string             $dir      Directory to load configs from
	 * @param ContainerInterface $services The service container to add configs to
	 * @param Environment        $env      The environment object
	 */
	public function __construct($dir, ContainerInterface $services, Environment $env)
	{
		$this->_dir      = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->_services = $services;
		$this->_env      = $env;
	}

	public function load(Registry $registry)
	{
		$configs = array();
		$dirs    = array(
			$this->_dir,
			$this->_dir . $this->_env->get() . '/',
			$this->_dir . $this->_env->get() . '/' . $this->_env->installation() . '/',
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

				$name = $file->getBasename('.yml');

				if (!isset($configs[$name])) {
					$configs[$name] = new Compiler;
				}

				$configs[$name]->add(file_get_contents($file->getPathname()));
			}
		}

		foreach ($configs as $name => $compiler) {
			$registry->$name = $compiler->compile();
		}

		return $registry;
	}
}