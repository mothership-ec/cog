<?php

namespace Message\Cog\DB;

use Message\Cog\DB\Adapter\ConnectionInterface;
use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryParser;
use Message\Cog\DB\Query;

/**
 * Builds a database query by concatenating query elements given.
 *
 * @author Eleanor Shakeshaft <eleanor@message.co.uk>
 */
class QueryBuilder implements QueryBuilderInterface
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

	const INNER_JOIN_TAG = 0;
	const LEFT_JOIN_TAG = 1;

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

	private $_params = [];

	public function __construct(ConnectionInterface $connection, QueryParser $parser)
	{
		$this->_connection = $connection;
		$this->_parser = $parser;
	}

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
			$this->_selectExpr = array_merge($this->_selectExpr, $select);
		} else {
			$this->_selectExpr[] = (string) $select;
		}

		return $this;
	}

	/**
	 * Table name alias and the table reference. If left blank it'll use the alias.
	 *
	 * @param  string                           $alias      Name of the table or table alias
	 * @param  QueryBuilder | string            $table      QueryBuilder to provide data
	 *
	 * @return QueryBuilder                     $this
	 */
	public function from($alias, $table = null)
	{
		if (!is_string($alias)) {
			throw new \InvalidArgumentException('Alias must be instance of string');
		}

		if ($table) {
			$this->_from['table_reference'] = $table;
			$this->_from['alias'] = $alias;
		} else {
			$this->_from['table_reference'] = $alias;
			$this->_from['alias'] = false;
		}

		return $this;
	}

	/**
	 * Joins $table as $alias using $statement.
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $statement   Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 * @param  bool                             $on          Set to true if the join uses the ON clause, and false if
	 *                                                       it uses the USING clause
	 *
	 * @return QueryBuilder                     $this
	 */
	public function join($alias, $statement, $table = null, $on = true)
	{
		if (!is_string($alias)) {
			throw new \InvalidArgumentException('Alias must be a string');
		}

		if (!is_string($statement)) {
			throw new \InvalidArgumentException('On statement must be a string');
		}

		$statement = $on ? $statement : '(' . $statement . ')';

		if ($table) {
			$this->_join[] = [
				'table_reference' => $table,
				'statement'       => $statement,
				'alias'           => $alias,
				'type'            => self::INNER_JOIN_TAG,
				'clause'          => $on ? 'ON' : 'USING'
			];
		} else {
			$this->_join[] = [
				'table_reference' => $alias,
				'statement'       => $statement,
				'type'            => self::INNER_JOIN_TAG,
				'clause'          => $on ? 'ON' : 'USING'
			];
		}

		return $this;
	}

	/**
	 * Join using the ON clause
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $statement   Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 *
	 * @return QueryBuilder
	 */
	public function joinOn($alias, $statement, $table = null)
	{
		return $this->join($alias, $statement, $table, true);
	}

	/**
	 * Join using the USING clause
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $statement   Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 *
	 * @return QueryBuilder
	 */
	public function joinUsing($alias, $statement, $table = null)
	{
		return $this->join($alias, $statement, $table, false);
	}

	/**
	 * Joins $table as $alias using $onStatement.
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $statement   Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 * @param  bool                             $on          Set to true if the join uses the ON clause, and false if
	 *                                                       it uses the USING clause
	 *
	 * @return QueryBuilder                     $this
	 */
	public function leftJoin($alias, $statement, $table = null, $on = true)
	{
		if (!is_string($alias)) {
			throw new \InvalidArgumentException('Alias must be a string');
		}

		if (!is_string($statement)) {
			throw new \InvalidArgumentException('On statement must be a string');
		}

		$statement = $on ? $statement : '(' . $statement . ')';

		if ($table) {
			$this->_join[] = [
				'table_reference' => $table,
				'statement'       => $statement,
				'alias'           => $alias,
				'type'            => self::LEFT_JOIN_TAG,
				'clause'          => $on ? 'ON' : 'USING'
			];
		} else {
			$this->_join[] = [
				'table_reference' => $alias,
				'statement'       => $statement,
				'type'            => self::LEFT_JOIN_TAG,
				'clause'          => $on ? 'ON' : 'USING'
			];
		}

		return $this;
	}

	/**
	 * Left join using the ON clause
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $statement   Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 *
	 * @return QueryBuilder
	 */
	public function leftJoinOn($alias, $statement, $table = null)
	{
		return $this->leftJoin($alias, $statement, $table, true);
	}

	/**
	 * Left join using the USING clause
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $statement   Statement to join on
	 * @param  QueryBuilder | string            $table       Data / table
	 *
	 * @return QueryBuilder
	 */
	public function leftJoinUsing($alias, $statement, $table = null)
	{
		return $this->leftJoin($alias, $statement, $table, false);
	}

	/**
	 * Builds into the where statement using "AND" as default.
	 *
	 * @param  string | \Closure $statement the statement to append
	 * @param  array             $variables variable to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 *
	 * @return QueryBuilder                 $this
	 */
	public function where($statement, array $variables = [], $and = true)
	{
		if (empty($this->_where)) {
			$this->_where = $this->_parser->parse($statement, $variables);
		} else {
			$this->_where .= PHP_EOL . ($and ? 'AND ' : 'OR ') . $this->_parser->parse($statement, $variables);
		}

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
		if (is_array($groupBy)) {
			$this->_groupBy = array_merge($this->_groupBy, $groupBy);
		} else {
			$this->_groupBy[] = (string) $groupBy;
		}

		return $this;
	}

	/**
	 * Builds into the having statement using and as default.
	 *
	 * @param  string | closure  $statement the statement to append
	 * @param  array             $variable  variable to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 *
	 * @return QueryBuilder                 $this
	 */
	public function having($statement, array $variables = [], $and = true)
	{
		if (empty($this->_having)) {
			$this->_having = $this->_parser->parse($statement, $variables);
		} else {		
			$this->_having .= PHP_EOL . ($and ? 'AND ' : 'OR ') . $this->_parser->parse($statement, $variables);
		}

		return $this;
	}

	/**
	 * Orders by fields in $orderBy
	 *
	 * @param  string | array        $orderBy Fields to order by
	 *
	 * @return QueryBuilder                   $this
	 */
	public function orderBy($orderBy)
	{
		if (is_array($orderBy)) {
			$this->_orderBy = array_merge($this->_orderBy, $orderBy);
		} else {
			$this->_orderBy[] = (string) $orderBy;
		}

		return $this;
	}

	/**
	 * Limits query to $limit rows
	 *
	 * @param  int $limit the limit
	 *
	 * @return QueryBuilder $this
	 */
	public function limit($offset, $limit = null)
	{
		// first param is the to limit if only one param set
		if ($limit === null) {
			$limit = $offset;
			$offset = null;
		}

		if (!is_int($limit)) {
			throw new \InvalidArgumentException('Limit must be of type integer');
		}

		if (!is_int($offset) && $offset !== null) {
			throw new \InvalidArgumentException('Offset must be of type integer or null');
			
		}

		$this->_limit = $offset ? "$offset, $limit" : $limit;

		return $this;
	}

	/**
	 * Builds UNION block takes [$var1, [$var2, [$var3...]...]...] each being
	 * QueryBuilder
	 *
	 * @return QueryBuilder  $this
	 */
	public function union(QueryBuilderInterface $queryBuilder)
	{
		foreach (func_get_args() as $union) {
			if (!($union instanceof QueryBuilder)) {
				throw new \InvalidArgumentException('Union args must be of type QueryBuilder');
			}
			if($this->_validateQueryBuilder($union)) {
				$this->_union[] = $union;
			}
		}

		return $this;
	}

	/**
	 * Builds UNION block takes [$var1, [$var2, [$var3...]...]...] each being
	 * QueryBuilder
	 *
	 * @return QueryBuilder  $this
	 */
	public function unionAll(QueryBuilderInterface $queryBuilder)
	{
		foreach (func_get_args() as $union) {
			if (!($union instanceof QueryBuilder)) {
				throw new \InvalidArgumentException('Union all args must be of type QueryBuilder');
			}
			if($this->_validateQueryBuilder($union)) {
				$this->_unionAll[] = $union;
			}
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
		$this->_selectExpr   = NULL;
		$this->_distinct     = NULL;
		$this->_from         = NULL;
		$this->_join         = [];
		$this->_leftJoin     = [];
		$this->_union        = [];
		$this->_unionAll     = [];
		$this->_where        = NULL;
		$this->_groupBy      = [];
		$this->_orderBy      = [];
		$this->_having       = NULL;
		$this->_limit        = NULL;
		$this->_query        = NULL;

		return $this;
	}

	/**
	 * Add parameters ad hoc to the query string for parsing when `getQueryString()` is called
	 *
	 * @param array $params
	 *
	 * @return QueryBuilder			Return $this for chainability
	 */
	public function addParams(array $params)
	{
		$this->_params = array_merge($this->_params, $params);

		return $this;
	}

	/**
	 * Calls `run()` on the instanciated `Query` object and returns the result
	 *
	 * @return Result
	 */
	public function run()
	{
		return $this->getQuery()->run();
	}

	/**
	 * Creates and returns the Query object to execute
	 *
	 * @return Query
	 */
	public function getQuery()
	{
		return new Query($this->_connection, $this->_parser, $this->getQueryString());
	}

	/**
	 * Gets the query as an string
	 *
	 * @return string
	 */
	public function getQueryString()
	{
		// SELECT_EXPRs, there must be at least one.
		if (empty($this->_selectExpr)) {
			// If no select expressions then possible that only using UNION
			if (!empty($this->_union)) {
				$select = array_shift($this->_union);
			} elseif(!empty($this->_unionAll)){
				$select = array_shift($this->_unionAll);
			}else{
				throw new \InvalidArgumentException('The query must have at least one select expression.');
			}

			$this->_query = $select->getQueryString();
		} else {
			// Must have a from expression when using SELECT query
			if (empty($this->_from)) {
				throw new \InvalidArgumentException("There must be at least one 'from' expression when calling select");
			}

			// SELECT (and optional DISTINCT)
			$this->_query = self::SELECT . ($this->_distinct ? " " . self::DISTINCT : '');

			$this->_query .= PHP_EOL . implode("," . PHP_EOL, $this->_selectExpr);

			// FROM
			$this->_query .= PHP_EOL . self::FROM;

			// TABLE REFERENCE
			if ($this->_from['table_reference'] instanceof QueryBuilder) {
				$this->_query .= " (" . $this->_from['table_reference']->getQueryString() . ")";
			} else {
				$this->_query .= " " . $this->_from['table_reference'];
			}
				// TABLE ALIAS
			if (!empty($this->_from['alias'])) {
				$this->_query .= " " . $this->_from['alias'];
			}

			// JOIN
			foreach ($this->_join as $join) {
				$this->_query .= PHP_EOL;

				switch ($join['type']) {
					case self::INNER_JOIN_TAG :
						$this->_query .= self::JOIN;
						break;
					case self::LEFT_JOIN_TAG :
						$this->_query .= self::LEFT_JOIN;
						break;
					default:
						throw new \LogicException('Join type not set');
				}

				if ($join['table_reference'] instanceof QueryBuilder) {
					$this->_query .= " (" . $join['table_reference']->getQueryString() . ")";
				} else {
					$this->_query .= " " . $join['table_reference'];
				}

				// TABLE_ALIAS
				if (isset($join['alias'])) {
					$this->_query .= " " . $join['alias'];
				}

				$this->_query .= " ". $join['clause'] . " " . $join['statement'];
			}
		}

		// WHERE
		if ($this->_where) {
			$this->_query .= PHP_EOL . self::WHERE . PHP_EOL . $this->_where;
		}

		// GROUP BY
		if (!empty($this->_groupBy)) {
			$this->_query .= PHP_EOL . self::GROUP_BY;
			$this->_query .= " " . implode(", ", $this->_groupBy);
		}

		// WHERE
		if (!empty($this->_having)) {
			$this->_query .= PHP_EOL . self::HAVING . PHP_EOL . $this->_having;
		}

		// ORDER BY
		if (!empty($this->_orderBy)) {
			$this->_query .= PHP_EOL . self::ORDER_BY;
			$this->_query .= " " . implode(", ", $this->_orderBy);
		}

		// LIMIT
		if ($this->_limit) {
			$this->_query .= PHP_EOL . self::LIMIT . " " . $this->_limit;
		}

		// UNION
		foreach ($this->_union as $union) {
			$this->_query .= PHP_EOL . self::UNION . PHP_EOL . $union->getQueryString();
		}

		// UNION ALL
		foreach ($this->_unionAll as $union) {
			$this->_query .= PHP_EOL . self::UNION_ALL . PHP_EOL . $union->getQueryString();
		}

		return $this->_parser->parse($this->_query, $this->_params);
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
