<?php

namespace Message\Cog\DB\Adapter\Faux;

use Message\Cog\DB\Adapter\ResultInterface;
use Message\Cog\DB\Adapter\ConnectionInterface;

/**
*
*/
class Result implements ResultInterface
{
	protected $_data;

	public function __construct($data, ConnectionInterface $connection)
	{
		// Ensure that the pointer is at the start of the array
		reset($data);
		
		$this->_data       = $data;
		$this->_connection = $connection;
	}

	public function fetchArray()
	{
		// Get the data at the current array pointer and move it forward one place
		$result = current($this->_data);
		next($this->_data);

		return $result;
	}

	public function fetchObject()
	{
		// Get the result as an array
		$result = $this->fetchArray();

		// If the result is false, dont try and convert it to an array
		return $result === false ? false : (object)$result;
	}

	public function seek($position)
	{
		// Move the pointer back to the start
		reset($this->_data);
		
		// Move it forward $position number of places
		for($i = 0; $i < $position; $i++) {
			next($this->_data);
		}

		return current($this->_data);
	}

	public function numRows()
	{
		return count($this->_data);
	}

	public function getAffectedRows()
	{
		return $this->_connection->getAffectedRows();
	}

	public function getLastInsertId()
	{
		return $this->_connection->getInsertId();
	}

	public function getTransactionStart()
	{
		return '';
	}

	public function getTransactionEnd()
	{
		return '';
	}

	public function getTransactionRollback()
	{
		return '';
	}

	public function getLastInsertIdFunc()
	{
		return '';
	}
}