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

		// Set to natively return integer & float types where appropriate
		if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
			$this->_handle->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
		}

		// Set the charset
		if(isset($this->_params['charset'])) {
			$this->_handle->set_charset($this->_params['charset']);
		}
	}

	public function query($sql)
	{
		$this->_connect();

		if($res = $this->_handle->query($sql)) {
			return new Result($res, $this);
		}

		return false;
	}

	public function escape($text)
	{
		$this->_connect();

		return $this->_handle->real_escape_string($text);
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