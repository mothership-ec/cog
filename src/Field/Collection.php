<?php

namespace Message\Cog\Field;

class Collection implements \IteratorAggregate, \Countable
{
	protected $_fields;

	public function __construct(array $fields)
	{
		foreach ($fields as $field) {
			$this->add($field);
		}
	}

	public function __get($type)
	{
		return $this->get($type);
	}

	public function add(Field $field)
	{
		$this->_fields[$field->getFieldType()]	= $field;
	}

	public function get($type)
	{
		if ($this->exists($type)) {
			return $this->_fields[$type];
		}

		throw new \Exception('No field of type `' . $type . '` set in collection');
	}

	public function count()
	{
		return count($this->_fields);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_fields);
	}

	public function exists($name)
	{
		return isset($this->_fields[$name]);
	}

	public function all()
	{
		return $this->_fields;
	}
}