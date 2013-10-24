<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Result;

class ArrayAdapter implements AdapterInterface
{

	protected $array;

	public function __construct(array $array = null)
	{
		$this->array = $array;
	}

	public function setArray(array $array)
	{
		$this->array = $array;
	}

	public function getArray()
    {
        return $this->array;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return count($this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        return array_slice($this->array, $offset, $length);
    }

}