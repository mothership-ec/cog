<?php

namespace Message\Cog\ValueObject;


// TODO: add tests to ensure add/set methods return $this
// TODO: add tests for sort by value
// TODO: add tests for sort by key
// TODO: add tests for calling setSort() with a "by" that is not valid
// TODO: add tests for calling validators (that will both fail and succeed)
// TODO: add test to ensure setting sorting after adding some items triggers a re-sort
// TODO: docblock new stuff
// TODO: test configure is called at the start of __construct
class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	const SORT_KEY   = 'sort';
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

	public function setSort(callable $sorter, $by = self::SORT_VALUE)
	{
		// TODO: check $type == 'key' or 'value'

		$this->_sort   = $sorter;
		$this->_sortBy = $by;

		$this->_sort();

		return $this;
	}

	public function addValidator(callable $validator)
	{
		if ($this->count() > 0) {
			throw new \LogicException(sprintf('Cannot set type "%s" on a non-empty collection', $this->_type));
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
	 * @return
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

// $collection = new Collection;
// $collection->setType('\\Message\\Mothership\\Epos\\Branch\\Branch'); // or setHint() or setTypeHint()
// $collection->setSort(function($a, $b) {
// 	$aKey = $a->authorship->createdAt();
// 	$bKey = $b->authorship->createdAt();

// 	if ($aKey === $bKey) {
// 		return 0;
// 	}

// 	return ($aKey < $bKey) ? -1 : 1;
// });

// $collection->addValidation(function($item) {
// 	if ($item->thing == 'somthing') {
// 		throw new Exception();
// 	}
// });

// $collection->setKey(function ($item) {
// 	return $item->getSpecialIdentifier();
// });

// $collection->setKey('id');

// class MyCollection extends Collection
// {
// 	protected function _configure()
// 	{
// 		$this->setKey('id')
// 		$this->setType('\\Message\\Mothership\\Epos\\Branch\\Branch');
// 		$this->setValidate(function($item) {
// 			if (!$item->method) {
// 				return false;
// 			}
// 		});

// 		$this->setValidate([$this, '_myspecialsort']);
// 	}

// 	protected function _myspecialsort($item)
// 	{
// 		if (!$item->method) {
// 			return false;
// 		}
// 	}
// }