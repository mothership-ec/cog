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

		if(isset($this->_params['lazy']) && $this->_params['lazy'] === false) {
			$this->_connect();
		}
	}

	protected function _connect()
	{
		// If we've already got a connection handle we don't 
		// need to connect again
		if($this->_handle !== null) {
			return;
		}

		// Make the connection
		$this->_handle = new \MySQLi(
			$this->_params['host'], 
			$this->_params['user'], 
			$this->_params['password'], 
			$this->_params['db']
		);

		// Set the charset
		if(isset($this->_params['charset'])) {
			$mysqli->set_charset($this->_params['charset']);	
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

	public function getHandle()
	{
		return $this->_handle;
	}

}