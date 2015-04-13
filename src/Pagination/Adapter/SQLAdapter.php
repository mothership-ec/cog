<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Query;

class SQLAdapter implements AdapterInterface
{

	protected $_query;
	protected $_sql;
	protected $_params;
	protected $_count;
	protected $_countSql;
	protected $_countParams;
	protected $_countColumn;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * Set the query which returns all unpaginated results.
	 *
	 * @param string $sql
	 * @param array  $params
	 */
	public function setQuery($sql, $params = array())
	{
		$this->_sql    = $sql;
		$this->_params = $params;
	}

	/**
	 * Set the query which will count how many unpaginated results are
	 * available. If this method is not called the adapter will attempt to
	 * generate a counting query from the main select query.
	 *
	 * @param string $sql
	 * @param array  $params
	 */
	public function setCountQuery($sql, $params = array())
	{
		$this->_countSql    = $sql;
		$this->_countParams = $params;
	}

	/**
	 * Set the column on which to count if using the generated count query.
	 *
	 * @param string $column
	 */
	public function setCountColumn($column)
	{
		$this->_countColumn = $column;
	}

	/**
     * {@inheritdoc}
     */
	public function getCount()
	{
		// Only run the count query once.
		if (null === $this->_count) {

			// If the count query sql has not been manually set.
			if (null === $this->_countSql) {

				// Get the column on which to attempt the count.
				$column = ($this->_countColumn) ?: 'id';

				// Generate the count query sql.
				$this->_countSql = preg_replace('/SELECT.*FROM/s', 'SELECT COUNT('.$column.') as `count` FROM', $this->_sql);

				$this->_countParams = $this->_params;
			}

			$this->_count = 0;
			if ($result = $this->_query->run($this->_countSql, $this->_countParams)) {
				$this->_count = count($result);
			}
		}

		return $this->_count;
	}

	/**
     * {@inheritdoc}
     */
	public function getSlice($offset, $length)
	{
		// Append a limit slice to the select query.
		$sql = $this->_sql . ' LIMIT ' . ($offset * $length) . ',' . $length;
		$slice = $this->_query->run($sql, $this->_params);

		return $slice;
	}

}