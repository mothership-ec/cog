<?php

namespace Message\Cog\Pagination\Adapter;

use Message\Cog\DB\Query;
use Pagerfanta\Adapter\AdapterInterface;

class SQLAdapter implements AdapterInterface {

	protected $_query;
	protected $_sql;
	protected $_count;
	protected $_countSql;
	protected $_countParams;
	protected $_countColumn;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function setQuery($sql, $params = array())
	{
		$this->_sql    = $sql;
		$this->_params = $params;
	}

	public function setCountQuery($sql, $params = array())
	{
		$this->_countSql    = $sql;
		$this->_countParams = $params;
	}

	public function setCountColumn($column)
	{
		$this->_countColumn = $column;
	}

	public function getNbResults()
	{
		if (null === $this->_count) {
			if (null === $this->_countSql) {
				$column = ($this->_countColumn) ?: 'id';
				$this->_countSql = preg_replace('/(SELECT)[^FROM]*(.*)/s', '$1 COUNT('.$column.') as `count` $2', $this->_sql);
				$this->_countParams = $this->_params;
			}

			$this->_count = 0;
			if ($count = $this->_query->run($this->_countSql, $this->_countParams)) {
				$this->_count = $count[0]->count;
			}
		}

		return $this->_count;
	}

	public function getSlice($offset, $length)
	{
		$sql = $this->_sql . ' LIMIT ' . $offset . ',' . $length;

		// d($sql);

		$slice = $this->_query->run($sql, $this->_params);

		return $slice;
	}

}