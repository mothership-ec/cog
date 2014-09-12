<?php

namespace Message\Cog\DB;

class QueryBuilder implements QueryBuilder
{
	const SELECT    = 'SELECT';
	const FROM      = 'FROM';
	const JOIN      = 'JOIN';
	const LEFT_JOIN = 'LEFT JOIN';
	const UNION     = 'UNION';
	const UNION_ALL = 'UNION ALL';
	const WHERE     = 'WHERE';
	const GROUP_BY  = 'GROUP BY';
	const ORDER_BY  = 'ORDER BY';
	const ORDER_BY  = 'ORDER BY';
	const HAVING    = 'HAVING';
	const LIMIT     = 'LIMIT';

	private $_start = [];
	private $_from = [];
	private $_join = [];
	private $_leftJoin = [];
	private $_union = [];
	private $_unionAll = [];
	private $_where = [];
	private $_groupBy = [];
	private $_orderBy = [];
	private $_having = [];
	private $_limit;



	/**
	 * Adds items used to build up select statement
	 *
	 * @param  string | array        $select Item(s) to add to select statement
	 *
	 * @return QueryBuilder                  $this
	 */
	public function select($select)
	{
		$this->_start[] = $select;

		return $this;
	}

	/**
	 * Table name and optional query to select from
	 *
	 * @param  string                $tableName  Name of the table
	 * @param  QueryBuilderInterface $builder    QueryBuilder to provide data
	 *
	 * @return QueryBuilder                      $this
	 */
	public function from($tableName, QueryBuilderInterface $builder = null)
	{
		$this->_from[$tableName] = $builder ?: $tableName;

		return $this;
	}

	/**
	 * Joins the current query to table $tableName or data specified by
	 * $builder as $tableName using $onStatement.
	 *
	 * @param  string                $tableName   name of the table
	 * @param  string                $onStatement statement to join on
	 * @param  QueryBuilderInterface $builder     optional way
	 *
	 * @return QueryBuilder                       $this
	 */
	public function join($tableName, $onStatement, QueryBuilderInterface $builder = null)
	{
		$this->_join[$tableName] = [
			['on'    => $onStatement],
			['table' => $builder ?: $tableName ]
		]

		return $this;
	}

	/**
	 * Joins the current query to table $tableName or data specified by
	 * $builder as $tableName using $onStatement.
	 *
	 * @param  string                $tableName   name of the table
	 * @param  string                $onStatement statement to join on
	 * @param  QueryBuilderInterface $builder     optional way
	 *
	 * @return QueryBuilder                       $this
	 */
	public function leftJoin($tableName, $onStatement, QueryBuilderInterface $builder = null)
	{
		$this->_leftJoin[$tableName] = [
			['on'    => $onStatement],
			['table' => $builder ?: $tableName ]
		]

		return $this;
	}

	/**
	 * [union description]
	 * @return [type] [description]
	 */
	public function union()
	{
		if ($this->_validate($item)) {
			$unions = FUNC_GET_ARGS();

		}

	}

	/**
	 * [unionAll description]
	 * @return [type] [description]
	 */
	public function unionAll()
	{

	}

	/**
	 * [where description]
	 * @param  [type]  $statement [description]
	 * @param  [type]  $variable  [description]
	 * @param  boolean $and       [description]
	 * @return [type]             [description]
	 */
	public function where($statement, $variable = null, $and = true)
	{

	}

	/**
	 * Groups by fields in $groupBy
	 *
	 * @param  string | array        $groupBy Fields to group by
	 *
	 * @return QueryBuilderInterface           $this
	 */
	public function groupBy($groupBy)
	{
		$this->_groupBy[] = $groupBy;

		return $this;
	}

	/**
	 * Orders by fields in $orderBy
	 *
	 * @param  string | array        $orderBy Fields to order by
	 *
	 * @return QueryBuilderInterface           $this
	 */
	public function orderBy($orderBy);
	{
		$this->_orderBy[] = $orderBy;

		return $this;
	}


	/**
	 * [having description]
	 * @param  [type]  $statement [description]
	 * @param  [type]  $variable  [description]
	 * @param  boolean $and       [description]
	 * @return [type]             [description]
	 */
	public function having($statement, $variable = null, $and = true)
	{

	}

	/**
	 * Limits query to $limit rows
	 *
	 * @param  int $limit the limit
	 *
	 * @return QueryBuilderInterface $this
	 */
	public function limit($limit)
	{
		$this->_limit = $limit;

		return $this;
	}


	/**
	 * [clear description]
	 * @return [type] [description]
	 */
	public function clear()
	{

	}

	/**
	 * [getQuery description]
	 * @return [type] [description]
	 */
	public function getQuery()
	{

	}

	/**
	 * [getQueryString description]
	 * @return [type] [description]
	 */
	public function getQueryString()
	{

	}


	/**
	 * Checks if item given is an instance of QueryBuilder.
	 *
	 * @param  mixed $item The item to validate
	 *
	 * @return bool False if it's not of type QueryBuilder
	 */
	private function _validateQueryBuilder($item)
	{
		if ($item instanceof QueryBuilder) {
			return true;
		}

		throw new \InvalidArgumentException('Is not an instance of QueryBuilder');
	}
}