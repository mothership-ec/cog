<?php

namespace Message\Cog\Migration\Collection;

/**
 * Parent collection class for collections.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_items = array();

	/**
	 * Constructor.
	 *
	 * @param array $items An array of items to add
	 */
	public function __construct(array $items = array())
	{
		foreach ($items as $item) {
			$this->add($item);
		}
	}

	/**
	 * Add an item to this collection.
	 *
	 * The items on this collection are sorted by code ascending immediately
	 * after the new item is added.
	 *
	 * @param CollectionItem $item The item to add
	 *
	 * @return Collection    Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If the item has no code set
	 * @throws \InvalidArgumentException If a item with the same code has
	 *                                   already been set on this collection
	 */
	public function add(Item $item)
	{
		if (!$item->code && 0 !== $item->code) {
			throw new \InvalidArgumentException(sprintf('Item `%s` has no code', $item->name));
		}

		if ($this->exists($item->code)) {
			throw new \InvalidArgumentException(sprintf(
				'Item code `%i` is already defined as `%s`',
				$item->code,
				$this->_items[$item->code]->name
			));
		}

		$this->_items[$item->code] = $item;

		ksort($this->_items);

		return $this;
	}

	/**
	 * Get an item set on this collection by the code.
	 *
	 * @param  int $code      The item code
	 *
	 * @return GroupInterface The group instance
	 *
	 * @throws \InvalidArgumentException If the item has not been set
	 */
	public function get($code)
	{
		if (!$this->exists($code)) {
			throw new \InvalidArgumentException(sprintf('Item code `%i` not set on collection', $code));
		}

		return $this->_items[$code];
	}

	/**
	 * Get all items set on this collection, where the keys are the item
	 * codes.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->_items;
	}

	/**
	 * Check if a given item code has been defined on this collection.
	 *
	 * @param  int $code The item code
	 *
	 * @return boolean   True if it exists, false otherwise
	 */
	public function exists($code)
	{
		return array_key_exists($code, $this->_items);
	}

	/**
	 * Get the number of items registered on this collection.
	 *
	 * @return int The number of items registered
	 */
	public function count()
	{
		return count($this->_items);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_items`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_items);
	}
}