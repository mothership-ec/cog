<?php

namespace Message\Cog\DB;

use Iterator;
use Countable;

/**
*
*/
abstract class ResultIterator implements Iterator, Countable
{
	protected $_result;
	private   $_position = 0;

	final public function rewind()
	{
		$this->_position = 0;
	}

	final public function reset()
	{
		$this->_result->seek(0);
	}

	final public function current()
	{
		$this->_result->seek($this->_position);

		return $this->_result->fetchObject();
	}

	final public function key()
	{
		return $this->_position;
	}

	final public function next()
	{
		++$this->_position;
	}

	final public function valid()
	{
		return $this->_position < $this->_result->numRows();
	}

	final public function count()
	{
		return $this->_result->numRows();
	}

	final public function row()
	{
		return $this->_result->fetchObject();
	}
}