<?php

namespace Message\Cog\ValueObject;

use Jeremeamia\SuperClosure\SerializableClosure;

use Closure;

/**
 * Represents a collection.
 *
 * @author Eleanor Shakeshaft <eleanor@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable, \ArrayAccess, \Serializable
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
	 * Constructor.
	 *
	 * Sorting defaults to sort by key ascending. Then `_configure()` is called
	 * to allow a subclass to configure the collection before the initial items
	 * are added.
	 *
	 * @param array $items Optional array of items to instantiate the collection
	 *                     with
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
	 * Get the data to be serialized when this class is serialized.
	 *
	 * All object properties are serialized. The `_key` property value, the
	 * `_sort` property value and the values of the `_validators` property are
	 * wrapped in `SerializableClosure` if they are an instance of `Closure`.
	 * This is because `Closure`'s can't be serialized.
	 *
	 * @see http://www.php.net/manual/en/class.serializable.php
	 *
	 * @return string Serialized data
	 */
	public function serialize()
	{
		if ($this->_key instanceof Closure) {
			$this->_key = new SerializableClosure($this->_key);
		}

		if ($this->_sort instanceof Closure) {
			$this->_sort = new SerializableClosure($this->_sort);
		}

		foreach ($this->_validators as $key => $validator) {
			if ($validator instanceof Closure) {
				$this->_validators[$key] = new SerializableClosure($validator);
			}
		}

		return serialize(get_object_vars($this));
	}

	/**
	 * Unserialize the data that was serialized and re-populate an instance of
	 * this class.
	 *
	 * Note that instances of `SerializableClosure` are left as they are and not
	 * converted back to `Closure` instances. This is because the
	 * `SerializableClosure` library doesn't seem to allow you to re-retrieve
	 * the original `Closure` properly.
	 *
	 * @see http://www.php.net/manual/en/class.serializable.php
	 *
	 * @param  string $data Serialized data
	 */
	public function unserialize($data)
	{
		$data = unserialize($data);

		foreach ($data as $name => $value) {
			$this->{$name} = $value;
		}
	}

	/**
	 * Set the collection key.
	 *
	 * If a string or integer is passed, this value will be used as the key if
	 * the collection values are arrays to retrieve the item's key. If the
	 * values are objects, the string or integer passed will be used as the
	 * property name to get the key from.
	 *
	 * If the key is passed as a Closure, the Closure will be passed the item
	 * each time we need to get the key and the return value will be used as
	 * the key.
	 *
	 * @param  string|int|Closure $key
	 *
	 * @return Collection              Returns $this for chainability
	 *
	 * @throws \LogicException If collection is not empty
	 */
	public function setKey($key)
	{
		if ($this->count() > 0) {
			throw new \LogicException(sprintf('Cannot set key "%s" on a non-empty collection', $this->_key));
		}

		$this->_key = $key;

		return $this;
	}

	/**
	 * Set the collection type. This must be a fully-qualified class name if the
	 * collection values will be instances of this class.
	 *
	 * @param  string $type     Fully-qualified class name
	 *
	 * @return Collection       Returns $this for chainability
	 *
	 * @throws \LogicException If collection is not empty
	 */
	public function setType($type)
	{
		if ($this->count() > 0) {
			throw new \LogicException(sprintf('Cannot set type "%s" on a non-empty collection', $this->_type));
		}

		$this->_type = $type;

		return $this;
	}

	/**
	 * Set how the collection is sorted.
	 *
	 * @param  callable $sorter Callable to sort the values, passed an $a and $b
	 *                          value
	 * @param  string   $by     What to sort by: `key` or `value`
	 *
	 * @return Collection       Returns $this for chainability
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
	 * Add some validation rules to this collection.
	 *
	 * The `$validator` argument will be executed each time a new value is added
	 * to this collection, with the first and only argument as the new value.
	 *
	 * If the validator wants to reject the value, it can either throw an
	 * exception or return false (which will throw an exception).
	 *
	 * @param  callable $validator Callable for the validator
	 *
	 * @return Collection          Returns $this for chainability
	 *
	 * @throws \LogicException If collection is not empty
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
	 * @throws \InvalidArgumentException If any validator returns false
	 * @throws \InvalidArgumentException If key is already set
	 * @throws \InvalidArgumentException If the value does not match the type
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
	 * @param  mixed $key Key of the item to be removed
	 *
	 * @return Collection Returns $this for chainability
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
	 * @param  mixed $key Key of the item to get
	 *
	 * @return mixed      Value of the item
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
	 * Checks if there is an item with the given key.
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
	 * @return boolean
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
	 * Setting a value on the collection using array notation is not allowed.
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
	 * @return array
	 */
	public function all()
	{
		return $this->_items;
	}

	/**
	 * Get the number of items in the collection
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
	 * A blank method which is called in the constructor after the default
	 * sorting has been applied.
	 *
	 * This method is only here to allow subclasses to extend it to configure
	 * the collection earlier than the initial items passed in the constructor
	 * are added.
	 */
	protected function _configure()
	{

	}

	/**
	 * Checks validation rules.
	 *
	 * @param  mixed $item The item to validate
	 *
	 * @return bool False if any validator returns false
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
	 *
	 * If the "sort by" is set to `key`, the `uksort` sorting function is used,
	 * otherwise `uasort` is used.
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
	 * @throws \InvalidArgumentException If the item is an array and does not
	 *                                   have the defined key
	 * @throws \InvalidArgumentException If the item is an object and does not
	 *                                   have the defined key as a property
	 * @throws \InvalidArgumentException If a key is defined as anything but a
	 *                                   callable and the value is neither an
	 *                                   array or an object
	 */
	private function _getKey($item)
	{
		if (null === $this->_key) {
			return false;
		}

		if ($this->_key instanceof Closure || $this->_key instanceof SerializableClosure) {
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
