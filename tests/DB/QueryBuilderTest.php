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

	public function testUnion()
	{
		$unionQuery = new QueryBuilder($this->_connect, $this->_parser);
		$unionQuery
			->select('*')
			->from('union_table')
		;

		$query = $this->_builder
			->select("*")
			->from('table')
			->union()
			->getQueryString()
		;


		$expected = "SELECT * FROM table UNION (SELECT * FROM union_table)";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}
}