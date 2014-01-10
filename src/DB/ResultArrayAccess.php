<?php

namespace Message\Cog\DB;

use ArrayAccess;

/**
*
*/
abstract class ResultArrayAccess extends ResultIterator implements ArrayAccess
{
	final public function offsetSet($offset, $value)
	{
		throw new \Exception('Cannot set array values on DB\Result');
	}

	final public function offsetExists($offset)
	{
		return ($this->offsetGet($offset) !== null);
	}

	final public function offsetUnset($offset)
	{
		throw new \Exception('Cannot unset array values on DB\Result');
	}

	final public function offsetGet($offset)
	{
		// store the old position so we can go back to it
		$oldPos = $this->_position;
		
		// get the desired row
		$this->_result->seek($offset);
		$row = $this->_result->fetchObject();
		
		// restore the position
		$this->_result->seek($oldPos);

		return $row === false ? null : $row;
	}
}