<?php

namespace Message\Cog\ValueObject;

/**
 * Represents a collection.
 *
 * @author Eleanor Shakeshaft <eleanor@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	const SORT_KEY   = 'key';
	const SORT_VALUE = 'value';

	private $_key;
	private $_type;
	private $_sort;
	private $_sortBy;
	private $_validators = [];

	private $_items = [];

	/**
	 * Configure collection.
	 *
	 * @param array $items
	 */
	public function __construct(array $items = [])
	{
		// Set default sorting to sort by key ascending
		$this->setSort(function($a, $b) {
			return ($a < $b) ? -1 : 1;
		}, self::SORT_KEY);

		// Allow subclass to configure the collection
		$this->_configure();

		foreach ($items as $item) {
			$this->add($item);
		}
	}

	/**
	 * Set the collection key.
	 *
	 * If the key is passed as a callable, the callable will be passed the item
	 * each time we need to get the key and the return value will be used as
	 * the key.
	 *
	 * @param  mixed $key
	 *
	 * @throws \LogicException If collection is not empty
	 */
	public function setKey($key)
	{
		if ($this->count() > 0 ) {
			throw new \LogicException(sprintf('Cannot set key "%s" on a non-empty collection', $this->_key));
		}

		$this->_key = $key;

		return $this;
	}

	/**
	 * Set the collection type.
	 *
	 * @param   mixed $key
	 *
	 * @throws  \LogicException If collection is not empty
	 */
	public function setType($type)
	{
		if ($this->count() > 0 ) {
			throw new \LogicException(sprintf('Cannot set type "%s" on a non-empty collection', $this->_type));
		}

		$this->_type = $type;

		return $this;
	}

	/**
	 * Set how the collection is sorted.
	 *
	 * @param  callable $sorter
	 * @param  string   $by
	 *
	 * @return Collection
	 *
	 * @throws  \LogicException If sort by is not by key or value
	 */
	public function setSort(callable $sorter, $by = self::SORT_VALUE)
	{
		if ($by != self::SORT_VALUE and $by != self::SORT_KEY) {
			throw new \LogicException('Sort by value must either be key or value');
		}

		$this->_sort   = $sorter;
		$this->_sortBy = $by;

		$this->_sort();

		return $this;
	}

	/**
	 * Adds validation rules to the collection.
	 *
	 * @param  callable $validator
	 *
	 * @return Collection
	 */
	public function addValidator(callable $validator)
	{
		if ($this->count() > 0) {
			throw new \LogicException('Cannot set validation rules on a non-empty collection');
		}

		$this->_validators[] = $validator;

		return $this;
	}

	/**
	 * Add an item to the collection.
	 *
	 * @param  mixed $item
	 *
	 * @return Collection
	 *
	 * @throws \InvalidArgumentException If validation is false
	 * @throws \InvalidArgumentException If key already set
	 * @throws \InvalidArgumentException If key isn't an instance of type
	 */
	public function add($item)
	{
		if (!$this->_validate($item)) {
			throw new \InvalidArgumentException('Does not meet validation requirements');
		}

		$key = $this->_getKey($item);

		if (null !== $this->_key && $this->exists($key) ) {
			throw new \InvalidArgumentException(sprintf('Item with key "%s" already set on collection', $this->_key));
		}

		if ($this->_type && !($item instanceof $this->_type)) {
			throw new \InvalidArgumentException(sprintf('Item must be an instance of "%s"', $this->_type));
		}

		if (false !== $key) {
			$this->_items[$key] = $item;
		}
		else {
			$this->_items[] = $item;
		}

		$this->_sort();

		return $this;
	}

	/**
	 * Remove an item from the collection.
	 *
	 * @param  mixed $key
	 *
	 * @return Collection
	 *
	 * @throws \InvalidArgumentException If key does not exist
	 */
	public function remove($key)
	{
		if (!$this->exists($key)) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" does not exist on collection', $this->_key));
		}

		unset($this->_items[$key]);

		return $this;
	}

	/**
	 * Get an item from the collection at the given key.
	 *
	 * @param  mixed $key
	 *
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException If key does not exist
	 */
	public function get($key)
	{
		if (!$this->exists($key)) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" does not exist on collection', $key));
		}

		return $this->_items[$key];
	}

	/**
	 * Checks if there are items with the given key.
	 *
	 * @param  mixed $key
	 *
	 * @return bool
	 */
	public function exists($key)
	{
		return array_key_exists($key, $this->_items);
	}

	/**
	 * @see    get
	 *
	 * @param  mixed $key
	 *
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * @see    exists
	 *
	 * @param  mixed $key
	 *
	 * @return mixed
	 */
	public function offsetExists($key)
	{
		return $this->exists($key);
	}

	/**
	 * @see    remove
	 *
	 * @param  mixed $key
	 *
	 * @return mixed
	 */
	public function offsetUnset($key)
	{
		return $this->remove($key);
	}

	/**
	 * This is not allowed.
	 *
	 * @param  mixed $key
	 * @param  mixed $value
	 *
	 * @throws \BadMethodCallException Always
	 */
	public function offsetSet($key, $value)
	{
		throw new \BadMethodCallException('Collections do not allow setting using array notation');
	}

	/**
	 * Get all items in collection.
	 *
	 * @return mixed
	 */
	public function all()
	{
		return $this->_items;
	}

	/**
	 * Count the items in the collection.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->all());
	}

	/**
	 * Get an iterator of the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->all());
	}

	/**
	 *
	 *
	 */
	protected function _configure()
	{

	}

	/**
	 * Checks validation rules.
	 *
	 * @return bool
	 */
	private function _validate($item)
	{
		foreach ($this->_validators as $validator) {
			if (false === call_user_func($validator, $item)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Sorts the collection by the defined comparison function.
	 * If sort by key use uksort, else use uasort to sort array & maintain index association.
	 */
	private function _sort()
	{

		if (self::SORT_KEY === $this->_sortBy) {
			uksort($this->_items, $this->_sort);
		}
		else {
			uasort($this->_items, $this->_sort);
		}
	}

	/**
	 * Get the key for an item.
	 *
	 * @param  mixed $item
	 *
	 * @return mixed|false
	 *
	 * @throws \InvalidArgumentException If
	 * @throws \InvalidArgumentException If
	 */
	private function _getKey($item)
	{
		if (null === $this->_key) {
			return false;
		}

		if ($this->_key instanceof \Closure) {
			return call_user_func($this->_key, $item);
		}

		if (is_array($item)) {
			if (!array_key_exists($this->_key, $item)) {
				throw new \InvalidArgumentException('Item does not have key');
			}

			return $item[$this->_key];
		}

		if (is_object($item)) {
			if (!property_exists($item, $this->_key)) {
				throw new \InvalidArgumentException('Item does not have key property');
			}

			return $item->{$this->_key};
		}

		throw new \InvalidArgumentException('Item must be array or object to have a key');
	}
}
