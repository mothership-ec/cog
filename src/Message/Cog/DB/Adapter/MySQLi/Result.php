<?php

namespace Message\Cog\DB\Adapter\MySQLi;

use Message\Cog\DB\Adapter\ResultInterface;
use Message\Cog\DB\Adapter\ConnectionInterface;

/**
*
*/
class Result implements ResultInterface
{
	protected $_handle;

	public function __construct($handle, ConnectionInterface $connection)
	{
		$this->_handle     = $handle;
		$this->_connection = $connection;
	}

	public function fetchArray()
	{
		return $this->_handle->fetch_array();
	}

	public function fetchObject()
	{
		return $this->_handle->fetch_object();
	}

	public function seek($position)
	{
		return $this->_handle->data_seek($position);
	}

	public function numRows()
	{
		return $this->_handle->num_rows;
	}

	public function getAffectedRows()
	{
		return $this->_connection->affected_rows;
	}

	public function getLastInsertId()
	{
		return $this->_connection->insert_id;
	}

	public function getTransactionStart()
	{
		return 'START TRANSACTION';
	}

	public function getTransactionEnd()
	{
		return 'COMMIT';
	}

	public function getTransactionRollback()
	{
		return 'ROLLBACK';
	}

	public function getLastInsertIdFunc()
	{
		return 'LAST_INSERT_ID()';
	}
}