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
	 * @param  string $name Configuration group identifier to get
	 * @return Group        Configuration group
	 *
	 * @throws Exception    If the configuration group does not exist
	 */
	public function __get($name)
	{
		if (!$this->_loaded) {
			$this->_load();
		}

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
	 * @param  string $offset Configuration identifier
	 * @return boolean        True if the configuration group exists
	 */
	public function offsetExists($offset)
	{
		return isset($this->_configs[$offset]);
	}

	/**
	 * Unset a configuration group.
	 *
	 * This is required because this class implements `\ArrayAccess`, but the
	 * functionality is not available. An exception will always be thrown.
	 *
	 * @param  string $offset Configuration identifier
	 * @throws Exception      Always: this method should not be called
	 */
	public function offsetUnset($offset)
	{
		throw new Exception('Config groups cannot be removed from the registry');
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
	 * @return array All configuration groups, where the keys are the identifiers
	 */
	public function getAll()
	{
		return $this->_configs;
	}

	/**
	 * Load the configurations using the configuration loader.
	 */
	public function _load()
	{
		$this->_loader->load($this);
		$this->_loaded = true;
	}
}