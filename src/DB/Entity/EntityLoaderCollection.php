<?php

namespace Message\Cog\DB\Entity;
 
/**
 * A collection of entity loaders used for lazy loading entities.
 * Puts together pairs of the plural name of the entity and its loader, e.g.
 * <pre>'items', new ItemLoader()</pre>
 */
class EntityLoaderCollection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	protected $_loaders = [];

	/**
	 * Constructor.
	 *
	 * @param array $items Optional array of items to instantiate the collection
	 *                     with
	 */
	public function __construct(array $loaders = [])
	{
		foreach ($loaders as $entityName => $loader) {
			$this->add($entityName, $loader);
		}
	}

	/**
	 * Add an item to the collection.
	 *
	 * @param  string                $entityName Name of the entity loaded by loader
	 * @param  EntityLoaderInterface $loader     Loader
	 *
	 * @throws \InvalidArgumentException If loader for $entityName is already set
	 *
	 * @return Collection Returns $this for chainability
	 */
	public function add($entityName, EntityLoaderInterface $loader)
	{
		if ($this->exists($entityName)) {
			throw new \InvalidArgumentException(
				sprintf("Can't add loader, because loader for %s already exists.", $entityName)
			);
		}

		$this->_loaders[$entityName] = $loader;

		return $this;
	}

	/**
	 * Remove a loader from the collection.
	 *
	 * @param  mixed $entityName Name of entity loaded by the loader to be removed
	 *
	 * @throws \InvalidArgumentException If loader for $entityName does not exist
	 * 
	 * @return Collection Returns $this for chainability
	 */
	public function remove($entityName)
	{
		if (!$this->exists($entityName)) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" does not exist on collection', $this->_key));
		}

		unset($this->_loaders[$entityName]);

		return $this;
	}

	/**
	 * Get a loader from the collection by the given entity name.
	 *
	 * @param  string $entityName Name of the entity loaded by the loader to be returned.
	 *
	 * @throws \InvalidArgumentException If loader does not exist
	 * 
	 * @return EntityLoaderInterface Entity loader
	 */
	public function get($entityName)
	{
		if (!$this->exists($entityName)) {
			throw new \InvalidArgumentException(sprintf('Loader for "%s" does not exist on collection', $entityName));
		}

		return $this->_loaders[$entityName];
	}

	/**
	 * Checks if there is a loader for the given entity name.
	 *
	 * @param  string $entityName
	 *
	 * @return bool
	 */
	public function exists($entityName)
	{
		return array_key_exists($entityName, $this->_loaders);
	}

	/**
	 * @see    get
	 *
	 * @param  string $entityName
	 *
	 * @return EntityLoaderInterface
	 */
	public function offsetGet($entityName)
	{
		return $this->get($entityName);
	}

	/**
	 * @see    exists
	 *
	 * @param  string $entityName
	 *
	 * @return bool
	 */
	public function offsetExists($entityName)
	{
		return $this->exists($entityName);
	}

	/**
	 * @see    remove
	 *
	 * @param  string $entityName
	 *
	 * @return EntityLoaderCollection
	 */
	public function offsetUnset($entityName)
	{
		return $this->remove($entityName);
	}

	/**
	 * Setting a value on the collection using array notation is not allowed.
	 *
	 * @param  mixed $entityName
	 * @param  mixed $value
	 *
	 * @throws \BadMethodCallException Always
	 */
	public function offsetSet($entityName, $value)
	{
		throw new \BadMethodCallException('Entity Loader Collections does not allow setting using array notation');
	}

	/**
	 * Get all loaders in collection.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->_loaders;
	}

	/**
	 * Get the number of loaders in the collection
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->all());
	}

	/**
	 * Get an iterator of the loaders.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->all());
	}
}
