<?php

namespace Message\Cog\DB;

interface QueryBuilderInterface
{

	/**
	 * Adds items used to build up select statement
	 *
	 * @param  string | array        $select    Item(s) to add to select statement
	 * @param  boolean               $distinct  exclude duplicates if true
	 *
	 * @return QueryBuilderInterface            $this
	 */
	public function select($select, $distinct = false);

	/**
	 * Table name alias and the table reference. If left blank it'll use the alias.
	 *
	 * @param  string                           $alias   Name of the table or table alias
	 * @param  QueryBuilderInterface | string   $table   Data / table
	 *
	 * @return QueryBuilderInterface            $this
	 */
	public function from($alias, $table = null);

	/**
	 * Joins $table as $alias using $onStatement.
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $onStatement Statement to join on
	 * @param  QueryBuilderInterface | string   $table       Data / table
	 *
	 * @return QueryBuilderInterface            $this
	 */
	public function join($alias, $onStatement, $table = null);

	/**
	 * Joins $table as $alias using $onStatement.
	 *
	 * @param  string                           $alias       Name of the table or table alias
	 * @param  string                           $onStatement Statement to join on
	 * @param  QueryBuilderInterface | string   $table       Data / table
	 *
	 * @return QueryBuilderInterface            $this
	 */
	public function leftJoin($alias, $onStatement, $table = null);

	/**
	 * Builds into the where statement using "AND" as default.
	 *
	 * @param  string | closure  $statement the statement to append
	 * @param  array             $variables variables to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 *
	 * @return QueryBuilderInterface         $this
	 */
	public function where($statement, array $variable = null, $and = true);

	/**
	 * Groups by fields in $groupBy
	 *
	 * @param  string | array        $groupBy Fields to group by
	 *
	 * @return QueryBuilderInterface           $this
	 */
	public function groupBy($groupBy);

	/**
	 * Builds into the having statement using "AND" as default.
	 *
	 * @param  string | closure  $statement the statement to append
	 * @param  array             $variables variables to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 *
	 * @return QueryBuilderInterface           $this
	 */
	public function having($statement, array $variables = null, $and = true);

	/**
	 * Orders by fields in $orderBy
	 *
	 * @param  string | array        $orderBy Fields to order by
	 *
	 * @return QueryBuilderInterface           $this
	 */
	public function orderBy($orderBy);

	/**
	 * Limits query to $limit rows
	 *
	 * @param  int $limit the limit
	 *
	 * @return QueryBuilderInterface $this
	 */
	public function limit($limit);

	/**
	 * Builds UNION block takes [$var1, [$var2, [$var3...]...]...] each being
	 * string or QueryBuilder
	 *
	 * @return QueryBuilderInterface $this
	 */
	public function union();

	/**
	 * Builds UNION block takes [$var1, [$var2, [$var3...]...]...] each being
	 * string or QueryBuilder
	 *
	 * @return QueryBuilderInterface $this
	 */
	public function unionAll();

	/**
	 * Creates and returns the Query object to execute
	 *
	 * @return Query
	 */
	public function getQuery();

	/**
	 * Gets the query as an unparsed string
	 *
	 * @return QueryBuilderInterface $this
	 */
	public function getQueryString();

	/**
	 * Clears all properties
	 *
	 * @return QueryBuilderInterface $this
	 */
	public function clear();
}