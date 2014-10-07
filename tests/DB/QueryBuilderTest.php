<?php

namespace Message\Cog\Test\DB;

use Message\Cog\DB\QueryBuilder;
use Mockery as m;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
	private $_builder;
	private $_parser;
	private $_connect;

	public function setUp()
	{
		$this->_parser  = m::mock('Message\Cog\DB\QueryParser');
		$this->_connect = m::mock('Message\Cog\DB\Adapter\ConnectionInterface');
		$this->_builder = new QueryBuilder($this->_connect, $this->_parser);
	}
	
	public function testSelectFromSimple()
	{
		$query = $this->_builder
			->select("*")
			->from('table')
			->getQueryString()
		;

		$expected = "SELECT * FROM table";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testMultiipleSelect()
	{
		$query = $this->_builder
			->select("col_1")
			->select("col_2")
			->select("col_3")
			->select("col_4")
			->from('table')
			->getQueryString()
		;

		$expected = "SELECT col_1, col_2, col_3, col_4 FROM table";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testSelectDistinct()
	{
		$query = $this->_builder
			->select("*", true)
			->from('table')
			->getQueryString()
		;

		$expected = "SELECT DISTINCT * FROM table";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testWhereSimple()
	{
		$this->_parser->shouldReceive('parse')->once()
			->with('col = variable', [])
			->andReturn('col = variable');
	
		$query = $this->_builder
			->select("*")
			->from('table')
			->where('col = variable')
			->getQueryString()
		;

		$expected = "SELECT * FROM table WHERE col = variable";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testWhereWithVariables()
	{
		$this->_parser->shouldReceive('parse')->once()
			->with('col = ?s', ["string"])
			->andReturn("col = 'string'");

		$this->_parser->shouldReceive('parse')->once()
			->with('col2 = ?i', [100])
			->andReturn('col2 = 100');

		$this->_parser->shouldReceive('parse')->once()
			->with('col3 = ?s', ['ORstring'])
			->andReturn("col3 = 'ORstring'");

		$query = $this->_builder
			->select("*")
			->from('table')
			->where('col = ?s', ["string"])
			->where('col2 = ?i', [100])
			->where('col3 = ?s', ['ORstring'], false)
			->getQueryString()
		;

		$expected = "SELECT * FROM table WHERE col = 'string' AND col2 = 100 OR col3 = 'ORstring'";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testGroupBy()
	{
		$query = $this->_builder
			->select("*")
			->from('table')
			->groupBy('col')
			->getQueryString()
		;

		$expected = "SELECT * FROM table GROUP BY col";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testGroupByMulti()
	{ 
		$query = $this->_builder
			->select("*")
			->from('table')
			->groupBy('col')
			->groupBy('col2')
			->getQueryString()
		;

		$expected = "SELECT * FROM table GROUP BY col, col2";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testOrderBy()
	{
		$query = $this->_builder
			->select("*")
			->from('table')
			->orderBy('col')
			->getQueryString()
		;

		$expected = "SELECT * FROM table ORDER BY col";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testOrderByMulti()
	{ 
		$query = $this->_builder
			->select("*")
			->from('table')
			->orderBy('col')
			->orderBy('col2')
			->getQueryString()
		;

		$expected = "SELECT * FROM table ORDER BY col, col2";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testLimit()
	{
		$query = $this->_builder
			->select("*")
			->from('table')
			->limit(2)
			->getQueryString()
		;

		$expected = "SELECT * FROM table LIMIT 2";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testOrderGroupLimit()
	{
		$query = $this->_builder
			->select("*")
			->from('table')
			->groupBy('col')
			->orderBy('col2')
			->groupBy('col1')
			->orderBy('col3')
			->limit(2)
			->getQueryString()
		;

		$expected = "SELECT * FROM table GROUP BY col, col1 ORDER BY col2, col3 LIMIT 2";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testFromAlias()
	{
		$query = $this->_builder
			->select("*")
			->from('alias', 'from_table')
			->getQueryString()
		;


		$expected = "SELECT * FROM from_table alias";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testFromAliasWithQB()
	{
		$fromTable = new QueryBuilder($this->_connect, $this->_parser);
		$fromTable
			->select('*')
			->from('from_table')
		;

		$query = $this->_builder
			->select("*")
			->from('alias', $fromTable)
			->getQueryString()
		;


		$expected = "SELECT * FROM (SELECT * FROM from_table) alias";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testFromWithUnion()
	{

		$table_b = new QueryBuilder($this->_connect, $this->_parser);
		$table_b
			->select('*')
			->from('table_b')
		;

		$table_a = new QueryBuilder($this->_connect, $this->_parser);
		$table_a
			->select('*')
			->from('table_a')
		;

		$fromTable = new QueryBuilder($this->_connect, $this->_parser);
		$fromTable
			->union($table_a)
			->union($table_b)
		;

		$query = $this->_builder
			->select("*")
			->from('alias', $fromTable)
			->getQueryString()
		;


		$expected = "SELECT * FROM (SELECT * FROM table_a UNION SELECT * FROM table_b) alias";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testHaving()
	{
		$this->_parser->shouldReceive('parse')->once()
			->with('col = variable', [])
			->passthru();
	
		$query = $this->_builder
			->select("*")
			->from('table')
			->having('col = variable')
			->getQueryString()
		;

		$expected = "SELECT * FROM table HAVING col = variable";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testHavingMulti()
	{
		$this->_parser->shouldReceive('parse')->times(3)
			->passthru();
	
		$query = $this->_builder
			->select("*")
			->from('table')
			->having('col_a = a')
			->having('col_b = b')
			->having('col_c = c', [], false)
			->getQueryString()
		;

		$expected = "SELECT * FROM table HAVING col_a = a AND col_b = b OR col_c = c";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testGetQuery()
	{
		$this->_connect->shouldReceive('query')->once()
			->andReturn(true)
		;
		$this->_parser->shouldReceive('parse')->once()
			->passthru()
		;


		$query = $this->_builder
			->select("*")
			->from('table')
			->having('col = variable')
			->getQuery()
			->run()	
		;

		$expected = "SELECT * FROM table WHERE col = variable";

		$this->assertEquals($expected, $query->getParsedQueryString());
	}
}