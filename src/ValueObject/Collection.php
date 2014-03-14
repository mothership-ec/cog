<?php

namespace Message\Cog\ValueObject;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess
{
	private $_items = [];

	public function __construct(array $items = [])
	{
		$this->configure();

		foreach ($items as $item) {
			$this->add($item);
		}
	}

	public function setKey($param)
	{

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

$collection = new Collection;
$collection->setType('\\Message\\Mothership\\Epos\\Branch\\Branch'); // or setHint() or setTypeHint()


$collection->setKey(function ($item) {
	return $item->id;
});

$collection->setKey('id');

class MyCollection extends Collection
{
	public function configure()
	{
		$this->setKey('id')
		$this->setType('\\Message\\Mothership\\Epos\\Branch\\Branch');
	}
}