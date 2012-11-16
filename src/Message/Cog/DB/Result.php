<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;
use Message\Cog\DB\Adapter\ResultInterface;

/**
*
*/
class Result extends ResultArrayAccess
{
	protected $_result;
	protected $_position = 0;
	protected $_affected = 0;
	protected $_insertId = 0;
	protected $_error    = '';

	public function __construct(ResultInterface $result)
	{
		$this->_result = $result;
		// snapshot these at this point
		$this->_affected = $result->getAffectedRows();
		$this->_insertId = $result->getLastInsertId();
	}

	public function value()
	{
		$this->reset();
		$first = $this->_result->fetchArray();

		return $first[0];
	}

	// TODO name this better? (cant be called row())
	public function first() 
	{
		$this->reset();
		$first = $this->_result->fetchArray();

		return $first;
	}

	public function hash($key = 0, $value = 1)
	{
		$this->_setDefaultKeys($key, $value);
		$hash = array();
		$this->reset();
		while($row = $this->_result->fetchArray()) {
			$hash[$row[$key]] = $row[$value];
		}

		return $hash;
	}

	public function transpose($key = null)
	{
		$this->_setDefaultKeys($key);
		$rows = array();
		$this->reset();
		while($row = $this->_result->fetchObject()) {
			$rows[$row->{$key}] = $row;
		}

		return $rows;
	}

	public function flatten($key = null)
	{
		$this->_setDefaultKeys($key);
		$rows = array();
		$this->reset();
		while($row = $this->_result->fetchArray()) {
			$rows[] = $row[$key];
		}

		return $rows;
	}

	public function bind($subject)
	{
		if(is_object($subject)) {
			// get the first row and bind it as the properties of the object
			$data = $this->_result->fetchArray();
			foreach($data as $key => $value) {
				$subject->{$key} = $value;
			}

			return $subject;
		}

		// Bind array of objects or classnames
		if(is_array($subject)) {
			foreach($subject as &$value) {
				$value = $this->bind($value);
			}

			return $subject;
		}
	}

	public function bindTo($subject)
	{
		// Valid class name
		if(is_string($subject)) {
			$class = new $subject;

			return $this->bind($class);
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