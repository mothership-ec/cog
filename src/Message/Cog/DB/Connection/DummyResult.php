<?php

namespace Message\Cog\DB\Connection;

/**
*
*/
class DummyResult
{
	protected $_pointer = null;

	public function __construct($result, $connection)
    {
        $this->_result   = $result;
        // snapshot these at this point
        $this->_affected = $connection->getAffectedRows();
        $this->_insertId = $connection->getLastInsertId();
        $this->_error    = $connection->getLastError();
    }

	public function fetch_array()
	{
		$row = $this->_prepare($this->_data[$this->_pos]);
		$this->_pointer++;

		return $row;
	}

	public function fetch_object()
	{
		if(($row = $this->fetch_array()) !== false) {
			return (object)$row;
		}

		return $row;
	}

	public function data_seek($pos) 
	{
		$this->_pointer = $pos;
	}

	protected function _prepare($data)
	{
		# code...
	}
}