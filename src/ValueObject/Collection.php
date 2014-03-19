<?php

namespace Message\Cog\ValueObject;

// TODO: implement type checking
// TODO: implement sorting
// TODO: implement key grabbing/checking
// TODO: write tests for the above
class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	private $_items = [];

	public function __construct(array $items = [])
	{
		$this->_configure();

		foreach ($items as $item) {
			$this->add($item);
		}
	}

	public function setKey($param)
	{
		// TODO: what happens if you call this after I have items?
	}

	public function setType($type)
	{

	}

	public function add($item)
	{
		// TODO: check for key collision, get the key, check the type

		$this->_items[] = $item;

		return $this;
	}

	public function remove($key)
	{
		// TODO: check it is actually set first

		unset($this->_items[$key]);

		return $this;
	}

	public function get($key)
	{
		// TODO: check exists

		return $this->_items[$key];
	}

	public function exists($key)
	{
		return array_key_exists($key, $this->_items);
	}

	public function offsetGet($key)
	{
		return $this->get($key);
	}

	public function offsetExists($key)
	{
		return $this->exists($key);
	}

	public function offsetUnset($key)
	{
		return $this->remove($key);
	}

	public function offsetSet($key, $value)
	{
		throw new \BadMethodCallException('Collections to not allow setting using array notation');
	}

	public function all()
	{
		return $this->_items;
	}

	public function count()
	{
		return count($this->all());
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->all());
	}


	protected function _sort()
	{
		ksort($this->all());
	}

	protected function _configure()
	{

	}

	protected function _validate($item)
	{

	}
}

// $collection = new Collection;
// $collection->setType('\\Message\\Mothership\\Epos\\Branch\\Branch'); // or setHint() or setTypeHint()


// $collection->setKey(function ($item) {
// 	return $item->id;
// });

// $collection->setKey('id');

// class MyCollection extends Collection
// {
// 	public function configure()
// 	{
// 		$this->setKey('id')
// 		$this->setType('\\Message\\Mothership\\Epos\\Branch\\Branch');
// 	}
// }