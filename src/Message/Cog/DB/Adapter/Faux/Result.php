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
		reset($data);
		$this->_data       = $data;
		$this->_connection = $connection;
	}

	public function fetchArray()
	{
		$result = current($this->_data);
		next($this->_data);

		return $result;
	}

	public function fetchObject()
	{
		return (object)$this->fetchArray();
	}

	public function seek($position)
	{
		reset($this->_data);

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
		return 0;
	}

	public function getLastInsertId()
	{
		return 1;
	}
}