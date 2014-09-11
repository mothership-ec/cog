<?php

namespace Message\Cog\DB;

interface QueryBuilderInterface
{
	/**
	 * Adds items used to build up select statement
	 * @param  string | array        $select Item(s) to add to select statement
	 * @return QueryBuilderInterface         $this
	 */
	public function select($select);

	/**
	 * Table name and optional query to select from, calling multiple 
	 * times will build up with union
	 * @param  string                $tableName  Name of the table
	 * @param  QueryBuilderInterface $builder    QueryBuilder to provide data
	 * @param  boolean               $unionAll   use UNION ALL if multiple from
	 * @return QueryBuilderInterface             $this
	 */
	public function from($tableName, QueryBuilderInterface $builder = null, $unionAll = true);

	/**
	 * Joins the current query to table $tableName or data specified by
	 * $builder as $tableName on $onStatement.
	 * @param  string                $tableName   name of the table
	 * @param  string                $onStatement statement to join on
	 * @param  QueryBuilderInterface $builder     optional way 
	 * @return QueryBuilderInterface              $this            
	 */
	public function join($tableName, $onStatement, QueryBuilderInterface $builder = null);

	/**
	 * Limits query to $limit rows
	 * @param  int $limit the limit
	 * @return QueryBuilderInterface $this
	 */
	public function limit($limit);

	/**
	 * Groups by fields in $groupBy
	 * @param  string | array        $groupBy Fields to group by
	 * @return QueryBuilderInterface           $this
	 */
	public function groupBy($groupBy);

	/**
	 * Orders by fields in $orderBy
	 * @param  string | array        $orderBy Fields to order by
	 * @return QueryBuilderInterface           $this
	 */
	public function orderBy($orderBy);


	/**
	 * Builds into the where statement using and as default.
	 * @param  string | closure  $statement the statement to append
	 * @param  mixed             $variable  variable to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 * @return QueryBuilderInterface         $this
	 */
	public function where($statement, $variable = null, $and = true);

	/**
	 * Builds into the having statement using and as default.
	 * @param  string | closure  $statement the statement to append
	 * @param  mixed             $variable  variable to substitute in
	 * @param  boolean           $and       append using and if true, or if false
	 * @return QueryBuilderInterface           $this
	 */
	public function having($statement, $variable = null, $and = true);

	/**
	 * Creates and returns the Query object to execute
	 * @return Query
	 */
	public function getQuery();

	/**
	 * Gets the query as an unparsed string
	 * @return QueryBuilderInterface $this
	 */
	public function getQueryString();

	/**
	 * Clears all properties
	 * @return QueryBuilderInterface $this
	 */
	public function clear();
}