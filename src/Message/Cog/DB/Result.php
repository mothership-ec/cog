<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;
use Message\Cog\DB\Adapter\ResultInterface;

/**
* Result class
*
* A wrapper around a database result. Instances of this class can be accessed
* like an array using indices and iterated over in a loop. There's also helper
* methods to make working with data easier.
*
* A word of warning: Because of the way the internal pointer works, using 
* a method on a Result object whilst iterating over it could lead to infinite 
* loops or unknown behavior.
*/
class Result extends ResultArrayAccess
{
	protected $_result;
	protected $_query;
	protected $_position = 0;
	protected $_affected = 0;
	protected $_insertId = 0;
	protected $_error    = '';

	public function __construct(ResultInterface $result, Query $query)
	{
		$this->_result = $result;
		$this->_query  = $query;
		// snapshot these at this point
		$this->_affected = $result->getAffectedRows();
		$this->_insertId = $result->getLastInsertId();
	}

	/**
	 * Returns the first field of the first row.
	 * 
	 * @return mixed The very first value in the dataset.
	 */
	public function value()
	{
		$this->reset();
		$first = $this->_result->fetchArray();

		return $first[0];
	}

	/**
	 * Return the first row in the dataset.
	 * 
	 * @return stdClass The first row.
	 */
	public function first() 
	{
		$this->reset();
		$first = $this->_result->fetchObject();

		return $first;
	}

	/**
	 * Returns a copy of the dataset as a hash where one field is used as the 
	 * key and another as the value.
	 * 
	 * @param  string $key   The column to use as the key. If omitted the first column is used.
	 * @param  string $value The column to use as the value. If omitted the second column is used.
	 * @return array         The generated hash.
	 */
	public function hash($key = null, $value = null)
	{
		$this->_setDefaultKeys($key, $value);
		$hash = array();
		$this->reset();
		while($row = $this->_result->fetchObject()) {
			$hash[$row->{$key}] = $row->{$value};
		}

		return $hash;
	}

	/**
	 * Get a copy of the dataset as an array with a chosen column as the key for each row.
	 * 
	 * @param  string $key The column name to use as the key for the array. If 
	 *                     omitted the first column is used.
	 * @return array       The dataset copy.
	 */
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

	/**
	 * Get a copy of the dataset as an array of arrays, where rows are combined using the specified key,
	 * 
	 * @param  string $key The column name to use as the key for each array. If omitted
	 *                     then the first column is used.
	 * @return array      An array of arrays.
	 */
	public function collect($key)
	{
		$this->_setDefaultKeys($key);
		$rows = array();
		$this->reset();

		while($row = $this->_result->fetchObject()) {
			if(!isset($rows[$row->{$key}])) {
				$rows[$row->{$key}] = array();
			}

			$rows[$row->{$key}][] = $row;
		}

		return $rows;
	}

	/**
	 * Reduce the columns in a resultset to a single value.
	 * 
	 * @param  string $key The column name to reduce to. If 
	 *                     omitted the first column is used.
	 * @return array       The flattened dataset.
	 */
	public function flatten($key = null)
	{
		$this->_setDefaultKeys($key);
		$rows = array();
		$this->reset();
		while($row = $this->_result->fetchObject()) {
			$rows[] = $row->{$key};
		}

		return $rows;
	}

	public function bind($subject)
	{
		if(is_object($subject)) {
			// get the first row and bind it as the properties of the object
			$data = $this->_result->fetchObject();
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

	/**
	 * Get the number of rows affected by the query which generated this result.
	 * 
	 * @return integer The number of affected rows.
	 */
	public function affected()
	{
		return $this->_affected;
	}

	/**
	 * Get the value last generated from an autoincrement column.
	 * 
	 * @return integer The last autoincrement value.
	 */
	public function id()
	{
		return $this->_insertId;
	}

	/**
	 * Get the names of the columns in the result as an array. If a parameter
	 * is passed then this is treated as an offset and the column name at that
	 * offset is returned. If the offset doesnt exist, false is returned.
	 * 	
	 * @param  integer $position The index offset of a single column name
	 * @return mixed      	     The array of column names or a single name.
	 */
	public function columns($position = null)
	{
		$this->reset();
		$columns = array_keys($this->row());

		if($position !== null) {
			return isset($columns[$position]) ? $columns[$position] : false;
		}

		return $columns;
	}

	/**
	 * Indicates if the query that generated this object was from a transaction
	 *
	 * @return boolean Indicates if object generated via transaction.
	 */
	public function isFromTransaction()
	{
		return $this->_query instanceof Transaction; 
	}

	/**
	 * Helper used to choose default key names for the transpose,
	 * flatten and hash methods if none are specified.
	 * 
	 * @param string $key   If null then the first column name in the dataset is used.
	 * @param string $value If null then the second column name in the dataset is used.
	 */
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