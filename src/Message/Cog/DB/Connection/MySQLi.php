<?php

namespace Message\Cog\DB\Connection;

/**
*
*/
class MySQLi implements ConnectionInterface
{
	protected $_handle = null;
	protected $_params = array();

	public function __construct(array $params)
	{
		$this->_params = $params;
	}

	protected function _connect()
	{
		if($this->_handle === null) {
			$this->_handle = new MySQLi($this->_params['host'], $this->_params['user'], $this->_params['password'], $this->_params['db']);
		}
	}

	public function query($sql)
	{
		$this->_connect();

		return $this->_handle->query($sql);
	}

	public function escape($text)
	{
		$this->_connect();

		return $this->real_escape_string($text);
	}

	public function getLastError()
	{
		$this->_connect();

		return $this->_handle->error;
	}

	public function getAffectedRows()
	{
		$this->_connect();

		return $this->_handle->affected_rows;
	}

	public function getLastInsertId()
	{
		$this->_connect();
		
		return $this->_handle->insert_id;
	}
}