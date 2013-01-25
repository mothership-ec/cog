<?php

namespace Message\Cog\Config;

use Message\Cog\Application\EnvironmentInterface;
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

	protected $_env;

	/**
	 * Constructor.
	 *
	 * @param string               $dir Directory to load configs from
	 * @param EnvironmentInterface $env The environment object
	 *
	 * @throws Exception                If the directory does not exist
	 */
	public function __construct($dir, EnvironmentInterface $env)
	{
		$this->_dir      = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->_env      = $env;

		if (!file_exists($this->_dir)) {
			throw new Exception(sprintf('Config directory `%s` does not exist', $this->_dir));
		}
	}

	/**
	 * Load configuration files from the directory.
	 *
	 * The base configuration files and any overwrite files for each
	 * configuration group are passed to the `Compiler`, and the compiled
	 * configuration `Group` instances are added to the supplied `Registry`
	 * instance.
	 *
	 * @param  Registry $registry The configuration registry to add compiled
	 *                            configurations to
	 * @return Registry           The same registry is returned
	 *
	 * @throws Exception          If any of the configuration directories are
	 *                            not readable
	 * @throws Exception          If any of the configuration files are not readable
	 */
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