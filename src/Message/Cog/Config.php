<?php

namespace Message\Cog;

use Symfony\Component\Yaml\Yaml;
use DirectoryIterator;
use InvalidArgumentException;
use stdClass;

/**
 * Config
 *
 * Manages installation specific variables for Cog applications.
 *
 * TODO: Lazy load configs as they are called for performance. This might have
 * an impact on how they get cached in ConfigCache.
 */
class Config
{
	protected $_configs = array();

	/**
	 * This is to handle old calls to Config::get()
	 * directly. This is being phased out and can
	 * be removed once everything uses Services.
	 */
	final public static function get($name)
	{
		return Services::get('config')->{$name};
	}

	public function __construct($path, $environment)
	{
		$this->_loadDirectory($path);
		$this->_loadDirectory(rtrim($path, '/') . '/' .$environment);
	}

	final public function __get($name)
	{
		if (!isset($this->_configs[$name])) {
			throw new InvalidArgumentException('Config type: `' . $name . '` doesnt exist.');
		}
		return $this->_configs[$name];
	}

	final public function __isset($name)
	{
		return isset($this->_configs[$name]);
	}

	final protected function _loadDirectory($path)
	{
		if (!file_exists($path)) {
			return false;
		}

		// loop over the files in the config directory
		$dir = new DirectoryIterator($path);

		foreach ($dir as $fileinfo) {

			if ($fileinfo->isDot() || $fileinfo->isDir() || $fileinfo->getExtension() !== 'yml') {
				continue;
			}

			// Converts the YAML file into an array
			$yaml 	   = Services::get('fns.utility')->arrayToObject(Yaml::parse($fileinfo->getPathname()), true);
			$shortName = $fileinfo->getBasename('.yml');


			if ($yaml === null) {
				$yaml = new stdClass;
			}

			$this->_initConfig($shortName, $yaml);

			// Transpose the properties onto the container class
			foreach ($yaml as $key => $value) {
				// IF THIS IS A CONFIG GROUP, AND IT'S ALREADY BEEN SET
				if (isset($this->_configs[$shortName]->{$key}) && is_object($this->_configs[$shortName]->{$key})) {
					// REPLACE ONLY THE NESTED ITEMS THAT ARE SET
					$this->_replaceSettingsRecursive($this->_configs[$shortName]->{$key}, $value);
				}
				else {
					if(is_array($this->_configs[$shortName])) {
						$this->_configs[$shortName][$key] = $value;
					} else {
						$this->_configs[$shortName]->{$key} = $value;
					}

				}
			}
		}
	}

	final protected function _initConfig($shortName, $yaml)
	{
		// TODO: make this find configs in modules. How?
		// This classname stuff smells bad. Having it hardcoded in this class
		// makes unit testing difficult.
		$className = '\\Mothership\\Framework\\Config\\'.ucfirst($shortName);

		if (!isset($this->_configs[$shortName])) {
			// If a config class exists use it instead.
			if(class_exists($className)) {
				$this->_configs[$shortName] = new $className;
			} else {
				$this->_configs[$shortName] = is_array($yaml) ? array() : new stdClass;
			}
		}
	}

	final protected function _replaceSettingsRecursive($first, $second)
	{
		// LOOP THROUGH FIRST GROUP
		foreach ((array) $first as $key => $value) {
			// IF THE KEY EXISTS IN THE SECOND GROUP
			if (isset($second->{$key})) {
				if (is_object($second->{$key})) {
					// IF THIS KEY IS ALSO A GROUP, CALL SELF
					$this->_replaceSettingsRecursive($first->{$key}, $second->{$key});
				}
				else {
					// ELSE APPLY NEW KEY ON SECOND GROUP TO FIRST GROUP
					$first->{$key} = $second->{$key};
				}
			}
		}
		return $first;
	}

}