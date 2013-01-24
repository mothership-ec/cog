<?php

namespace Message\Cog\Config;

/**
 * Configuration registry, holds all of the compiled configuration groups.
 *
 * The first time something tries to access a configuration group, the registry
 * tells the configuration loader (that is dependency injected) to load the
 * configurations. Otherwise known as "lazy loading"!
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Registry implements \IteratorAggregate, \ArrayAccess
{
	protected $_loader;
	protected $_loaded = false;

	protected $_configs = array();

	/**
	 * Constructor.
	 *
	 * @param LoaderInterface $loader Configuration loader to use
	 */
	public function __construct(LoaderInterface $loader)
	{
		$this->_loader = $loader;
	}

	/**
	 * Get a configuration group.
	 *
	 * If the configurations have not yet been loaded, calling this method will
	 * load them.
	 *
	 * @param  string $name Configuration group identifier to get
	 * @return Group        Configuration group
	 *
	 * @throws Exception    If the configuration group does not exist
	 */
	public function __get($name)
	{
		$this->_load();

		if (isset($this->_configs[$name])) {
			return $this->_configs[$name];
		}

		throw new Exception(sprintf('Config group `%s` does not exist.', $name));
	}

	/**
	 * Sets a configuration group.
	 *
	 * @param  string $name  Identifier for the configuration group
	 * @param  Group  $group Configuration group to set
	 *
	 * @throws Exception     If a config group is already defined for this identifier
	 */
	public function __set($name, Group $group)
	{
		if (isset($this->_configs[$name]))  {
			throw new Exception(sprintf('Config group `%s` has already been set', $name));
		}

		$this->_configs[$name] = $group;
	}

	/**
	 * Check if a configuration group exists.
	 *
	 * @param  string $name Configuration identifier
	 * @return boolean      True if the configuration group exists
	 */
	public function __isset($name)
	{
		return isset($this->_configs[$name]);
	}

	/**
	 * Unset a configuration group.
	 *
	 * This functionality is not available. An exception will always be thrown.
	 *
	 * @param  string $name Configuration identifier
	 * @throws Exception    Always: this method should not be called
	 */
	public function __unset($name)
	{
		throw new Exception('Config groups cannot be removed from the registry');
	}

	/**
	 * Get the iterator to use when iterating over this class.
	 *
	 * @return \ArrayIterator The iterator to use
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_configs);
	}

	/**
	 * Check if a configuration group exists when using array access.
	 *
	 * @see __isset
	 *
	 * @param  string $offset Configuration identifier
	 * @return boolean        True if the configuration group exists
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * Unset a configuration group using array access.
	 *
	 * @see __unset
	 *
	 * @param  string $offset Configuration identifier
	 * @throws Exception      Always: this method should not be called
	 */
	public function offsetUnset($offset)
	{
		return $this->__unset($offset);
	}

	/**
	 * Get a configuration group using array access.
	 *
	 * @see __get
	 *
	 * @param  string $offset Configuration identifier
	 * @return Group          Configuration group
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * Set a configuration group using array access.
	 *
	 * @see __set
	 *
	 * @param  string $offset Configuration identifier
	 * @return mixed          Configuration group
	 */
	public function offsetSet($offset, $value)
	{
		return $this->__set($offset, $value);
	}

	/**
	 * Get all configuration groups as an associative array.
	 *
	 * If the configurations have not yet been loaded, calling this method will
	 * load them.
	 *
	 * @return array All configuration groups, where the keys are the identifiers
	 */
	public function getAll()
	{
		$this->_load();

		return $this->_configs;
	}

	/**
	 * Load the configurations using the configuration loader.
	 *
	 * @return boolean True if the configuration was loaded, false if it had
	 *                 already been loaded
	 */
	public function _load()
	{
		if ($this->_loaded) {
			return false;
		}

		$this->_loader->load($this);
		$this->_loaded = true;

		return true;
	}
}