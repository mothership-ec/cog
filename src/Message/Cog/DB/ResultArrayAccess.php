<?php

namespace Message\Cog\DB;

use ArrayAccess;

/**
*
*/
abstract class ResultArrayAccess extends ResultIterator implements ArrayAccess
{
	public function offsetSet($offset, $value)
	{
		throw new Exception('Cannot set array values on DB\Result');
	}

	public function offsetExists($offset)
	{
		return ($this->offsetGet($offset) !== null);
	}

	public function offsetUnset($offset)
	{
		throw new Exception('Cannot unset array values on DB\Result');
	}

	public function offsetGet($offset)
	{
		// store the old position so we can go back to it
		$oldPos = $this->_position;
		// get the desired row
		$row = $this->_result->seek($offset);
		// restore the position
		$this->_result->seek($oldPos);

		return $row == false ? null : $row;
	}
}