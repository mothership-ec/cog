<?php

namespace Message\Cog\DB\Adapter\MySQLi;

use Message\Cog\DB\Adapter\ConnectionInterface;

/**
*
*/
class Connection implements ConnectionInterface
{
	protected $_handle = null;
	protected $_params = array();

	public function __construct(array $params = array())
	{
		$this->_params = $params;
	}

	protected function _connect()
	{
		if($this->_handle === null) {
			$this->_handle = new \MySQLi(
				$this->_params['host'], 
				$this->_params['user'], 
				$this->_params['password'], 
				$this->_params['db']
			);
		}
	}

	public function query($sql)
	{
		$this->_connect();

		if($res = $this->_handle->query($sql)) {
			return new Result($this->_handle->query($sql), $this);
		}

		return false;
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

}