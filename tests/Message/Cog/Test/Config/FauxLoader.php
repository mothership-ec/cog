<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\LoaderInterface;
use Message\Cog\Config\Registry;
use Message\Cog\Config\Group;

/**
 * An implementation of the configuration loader used for unit testing.
 *
 * This class allows you to pass in configuration groups with their relevant
 * names / identifiers that will be added to the registry when `load()` is
 * called. This is very useful when writing unit tests.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxLoader implements LoaderInterface
{
	protected $_configs = array();

	/**
	 * Add the configuration groups on this loader to a configuration registry.
	 *
	 * @param  Registry $registry The configuration registry to add configs to
	 * @return Registry           The same registry is returned
	 */
	public function load(Registry $registry)
	{
		foreach ($this->_configs as $name => $config) {
			$registry->$name = $config;
		}

		$this->clear();

		return $registry;
	}

	/**
	 * Add a configuration group to be set on the registry when `load()` is
	 * called.
	 *
	 * @param string $name  Identifier to use for the configuration group
	 * @param Group  $group The configuration group
	 */
	public function addConfig($name, Group $group)
	{
		$this->_configs[$name] = $group;
	}

	/**
	 * Add an array of configurations to be set on the registry when `load()` is
	 * called.
	 *
	 * The keys in the array are use as the configuration group identifiers.
	 *
	 * @param array $configs Associative array of identifiers & configuration groups
	 */
	public function addConfigs(array $configs)
	{
		foreach ($configs as $name => $config) {
			$this->addConfig($name, $config);
		}
	}

	/**
	 * Clear all configuration groups that have been added to this instance.
	 */
	public function clear()
	{
		$this->_configs = array();
	}
}