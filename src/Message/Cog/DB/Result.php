<?php

namespace Message\Cog\DB;

/**
*
*/
class Result extends ResultIterator
{
	protected $_result;
	protected $_position = 0;
	protected $_affected = 0;
	protected $_insertId = 0;
	protected $_error    = '';

	public function __construct($result, $connection)
    {
        $this->_result = $result;
        // snapshot these at this point
        $this->_affected = $connection->getAffectedRows();
        $this->_insertId = $connection->getLastInsertId();
        $this->_error    = $connection->getLastError();
    }

	public function value()
	{
		$this->reset();
		$first = $this->_result->fetch_array();
		return $first[0];
	}

	public function hash($key = 0, $value = 1)
	{
		$this->_setDefaultKeys($key, $value);
		$hash = array();
		$this->reset();
		while($row = $this->_result->fetch_array()) {
			$hash[$row[$key]] = $row[$value];
		}

		return $hash;
	}

	public function transpose($key = null)
	{
		$this->_setDefaultKeys($key);
		$rows = array();
		$this->reset();
		while($row = $this->_result->fetch_object()) {
			$rows[$row->{$key}] = $row;
		}

		return $rows;
	}

	public function flatten($key = null)
	{
		$this->_setDefaultKeys($key);
		$rows = array();
		$this->reset();
		while($row = $this->_result->fetch_array()) {
			$rows[] = $row[$key];
		}

		return $rows;
	}

	public function bind($subject)
	{
		if(is_object($subject)) {

		} else if(is_string($subject)) {

		}
	}

	public function affected()
	{
		return $this->_affected;
	}

	public function insertId()
	{
		return $this->_insertId;
	}

	public function columns($position = null)
	{
		$this->reset();
		$columns = array_keys($this->row());

		if($position !== null) {
			return isset($columns[$position]) ? $columns[$position] : false;
		}

		return $columns;
	}

	protected function _setDefaultKeys(&$key = null, &$value = null)
	{
		if($key === null) {
			$key = $this->columns(0);
		}

		if($value === null) {
			$value = $this->columns(1);
		}
	}
}