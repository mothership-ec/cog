<?php

namespace Message\Cog\DB;

class QueryBuilder implements QueryBuilder
{
	const SELECT    = 'SELECT';
	const DISTINCT  = 'DISTINCT';
	const FROM      = 'FROM';
	const JOIN      = 'JOIN';
	const LEFT_JOIN = 'LEFT JOIN';
	const UNION     = 'UNION';
	const UNION_ALL = 'UNION ALL';
	const WHERE     = 'WHERE';
	const GROUP_BY  = 'GROUP BY';
	const ORDER_BY  = 'ORDER BY';
	const HAVING    = 'HAVING';
	const LIMIT     = 'LIMIT';

	private $_selectExpr = [];
	private $_distinct;
	private $_from = [];
	private $_join = [];
	private $_leftJoin = [];
	private $_union = [];
	private $_unionAll = [];
	private $_where;
	private $_groupBy = [];
	private $_orderBy = [];
	private $_having;
	private $_limit;
	private $_query;


	/**
	 * Adds items used to build up select statement
	 *
	 * @param  string | array        $select    Item(s) to add to select statement
	 * @param  boolean               $distinct  exclude duplicates if true
	 *
	 * @return QueryBuilder                     $this
	 */
	public function select($select, $distinct = false)
	{
		$this->_distinct = $distinct;

		if (is_array($select)) {
			array_merge($this->_selectExpr[], $select);
		} else {
			$this->_selectExpr[] .= $select;
		}

		return $this;
	}

	/**
	 * Table name alias and the table reference. If left blank it'll use the alias.
	 *
	 * @param  string                           $tableName  Name of the table or table alias
	 * @param  QueryBuilder | string            $table      QueryBuilder to provide data
	 *
	 * @return QueryBuilder                     $this
	 */
	public function from($alias, $table = null);
	{
		if ($this->_validateQueryBuilder($alias)) {
			throw new \InvalidArgumentException('Alias must not be an instance of QueryBuilder');
		}

		if ($table) {
			$this->_from['table_reference'] = $table;
			$this->_from['alias'] = $alias;
		} else {
			$this->_from['table_reference'] = $alias;
		}

		return $this;
	}

	/**
	 * Joins $table as $alias using $onStatement.
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $onStatement Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 *
	 * @return QueryBuilder                     $this
	 */
	public function join($alias, $onStatement, $table = null)
	{
		if ($this->_validateQueryBuilder($alias)) {
			throw new \InvalidArgumentException('Alias must not be an instance of QueryBuilder');
		}

		if ($this->_validateQueryBuilder($onStatement)) {
			throw new \InvalidArgumentException('On statement must not be an instance of QueryBuilder');
		}

		if ($table) {
			$this->_join .= [
				'table_reference' => $table_reference,
				'on_statement'    => $onStatement,
				'alias'           => $alias,
			];
		} else {
			$this->_join .= [
				'table_reference' => $alias,
				'on_statement'    => $onStatement,
			];
		}

		return $this;
	}

	/**
	 * Joins $table as $alias using $onStatement.
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $onStatement Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 *
	 * @return QueryBuilder                     $this
	 */
	public function leftJoin($alias, $onStatement, $table = null)
	{
		if ($this->_validateQueryBuilder($alias)) {
			throw new \InvalidArgumentException('Alias must not be an instance of QueryBuilder');
		}

		if ($this->_validateQueryBuilder($onStatement)) {
			throw new \InvalidArgumentException('On statement must not be an instance of QueryBuilder');
		}

		if ($table) {
			$this->_leftJoin .= [
				'table_reference' => $table_reference,
				'on_statement'    => $onStatement,
				'alias'           => $alias,
			];
		} else {
			$this->_leftJoin .= [
				'table_reference' => $alias,
				'on_statement'    => $onStatement,
			];
		}

		return $this;
	}

	/**
	 * Builds into the where statement using "AND" as default.
	 *
	 * @param  string | closure  $statement the statement to append
	 * @param  mixed             $variable  variable to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 *
	 * @return QueryBuilder                 $this
	 */
	public function where($statement, $variable = null, $and = true)
	{
		if (empty($this->_where)) {
			$this->_where = $this->_parser->parse($statement, $variable);
		}

		$this->_where .= "/n" . $and ? 'AND ' : 'OR ' . $this->_parser->parse($statement, $variable);

		return $this;
	}

	/**
	 * Groups by fields in $groupBy
	 *
	 * @param  string | array        $groupBy Fields to group by
	 *
	 * @return QueryBuilder                   $this
	 */
	public function groupBy($groupBy)
	{
		$this->_groupBy[] .= $groupBy;

		return $this;
	}

	/**
	 * Builds into the having statement using and as default.
	 *
	 * @param  string | closure  $statement the statement to append
	 * @param  mixed             $variable  variable to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 *
	 * @return QueryBuilder                 $this
	 */
	public function having($statement, $variable = null, $and = true)
	{
		if (empty($this->_having)) {
			$this->_having = $this->_parser->parse($statement, $variable);
		}

		$this->_having .= "/n" . $and ? 'AND ' : 'OR ' . $this->_parser->parse($statement, $variable);

		return $this;
	}

	/**
	 * Orders by fields in $orderBy
	 *
	 * @param  string | array        $orderBy Fields to order by
	 *
	 * @return QueryBuilder                   $this
	 */
	public function orderBy($orderBy);
	{
		$this->_orderBy[] .= $orderBy;

		return $this;
	}

	/**
	 * Limits query to $limit rows
	 *
	 * @param  int $limit the limit
	 *
	 * @return QueryBuilder $this
	 */
	public function limit($limit)
	{
		$this->_limit = $limit;

		return $this;
	}

	/**
	 * Builds UNION block takes [$var1, [$var2, [$var3...]...]...] each being
	 * string or QueryBuilder
	 *
	 * @return QueryBuilder  $this
	 */
	public function union()
	{
		if ($this->_validateQueryBuilder($item)) {
			$unions = FUNC_GET_ARGS();

		}

		return $this;
	}

	/**
	 * Builds UNION block takes [$var1, [$var2, [$var3...]...]...] each being
	 * string or QueryBuilder
	 *
	 * @return QueryBuilder  $this
	 */
	public function unionAll()
	{
		if ($this->_validateQueryBuilder($item)) {
			$unions = func_get_args();

		}

		return $this;
	}

	/**
	 * Clears all properties
	 *
	 * @return QueryBuilder $this
	 */
	public function clear()
	{

		return $this;
	}

	/**
	 * Creates and returns the Query object to execute
	 *
	 * @return Query
	 */
	public function getQuery()
	{

	// CHECK IF QUERY HAS A DATA MANIPULATION STATEMENT
	// if (!$this->_queryType) {
	// 	throw new \InvalidArgumentException('The query must start with a data manipulation statement.');
	// }

	// IF SELECT

		// SELECT (and optional DISTINCT)
		$this->_query = self::SELECT . " " . if $this->_distinct :? self::DISTINCT;

		// SELECT_EXPRs, there must be at least one.
		if (empty($this->_selectExpr)) {
			throw new \InvalidArgumentException('The query must have at least one select expression.');
		}
		$this->_query = " /n" . implode(", /n", $this->_selectExpr);

		// FROM
		$this->_query = "/n" . self::FROM;

		// TABLE_REFERENCE
		if ($this->_from['table_reference'] instanceof QueryBuilder) {
			$this->_query = " (" . $this->_from['table_reference'] . ")";
		} else {
			$this->_query = " " . $this->_from['table_reference'];
		}
			// TABLE_ALIAS
		if ($this->_from['alias']) {
			$this->_query = " " . $this->_from['alias'];
		}

		// JOIN
		foreach ($this->_join as $join) {
			$this->_query = " /n" . self::JOIN;

			if ($join['table_reference'] instanceof QueryBuilder) {
				$this->_query = " (" . $join['table_reference'] . ")";
			} else {
				$this->_query = " " . $join['table_reference'];
			}

			// TABLE_ALIAS
			if ($join['alias']) {
				$this->_query = " " . $join['alias'];
			}

			$this->_query = " ON " . $join['on_statement'];
		}

		// LEFT JOIN
		foreach ($this->_leftJoin as $join) {
			$this->_query = " /n" . self::LEFT_JOIN;

			if ($join['table_reference'] instanceof QueryBuilder) {
				$this->_query = " (" . $join['table_reference'] . ")";
			} else {
				$this->_query = " " . $join['table_reference'];
			}

			// TABLE_ALIAS
			if ($join['alias']) {
				$this->_query = " " . $join['alias'];
			}

			$this->_query = " ON " . $join['on_statement'];
		}

		// WHERE
		if ($this->_where) {
			$this->_query = " /n" . self::WHERE . $this->_where;
		}

		// GROUP BY
		if (!empty($this->_groupBy)) {
			$this->_query = " /n" . self::GROUP_BY;
			$this->_query = " " . implode(", ", $this->_groupBy);
		}

		// WHERE
		if ($this->_having) {
			$this->_query = " /n" . self::HAVING . $this->_having;
		}

		// ORDER BY
		if (!empty($this->_orderBy)) {
			$this->_query = " /n" . self::ORDER_BY;
			$this->_query = " " . implode(", ", $this->_orderBy);
		}

		// LIMIT
		if ($this->_limit) {
			$this->_query = " /n" . self::LIMIT . " " . $this->_limit;
		}




		return new Query($this->getQueryString());
	}

	/**
	 * Gets the query as an unparsed string
	 *
	 * @return QueryBuilder  $this
	 */
	public function getQueryString()
	{

		$str .= $queryBuilder->getQueryString();
		return $this;
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

		return false;
	}
}