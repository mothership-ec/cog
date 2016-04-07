<?php

namespace Message\Cog\Test\DB;

use Message\Cog\DB\QueryBuilder;

class QueryBuilderTestRefactor extends \PHPUnit_Framework_TestCase
{
	private $_builder;
	private $_parser;
	private $_connect;

	public function setUp()
	{
		$this->_connect = $this->getMockBuilder('Message\\Cog\\DB\\Adapter\\ConnectionInterface')->getMock();
		$this->_parser  = $this->getMockBuilder('Message\\Cog\\DB\\QueryParser')->disableOriginalConstructor()->setMethods(null)->getMock();
		$this->_builder = new QueryBuilder($this->_connect, $this->_parser);
	}

	public function testSelectFromSimple()
	{
		$expected = "SELECT * FROM table";

		$query = $this->_builder
			->select("*")
			->from('table')
			->getQueryString()
		;

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testMultipleSelect()
	{
		$expected = "SELECT col_1, col_2, col_3, col_4 FROM table";

		$query = $this->_builder
			->select("col_1")
			->select("col_2")
			->select("col_3")
			->select("col_4")
			->from('table')
			->getQueryString()
		;

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testSelectArray()
	{
		$expected = "SELECT col_1, col_2, col_3, col_4 FROM table";

		$query = $this->_builder
			->select(["col_1", "col_2", "col_3", "col_4"])
			->from('table')
			->getQueryString()
		;

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testSelectDistinct()
	{
		$expected = "SELECT DISTINCT * FROM table";

		$query = $this->_builder
			->select("*", true)
			->from('table')
			->getQueryString()
		;

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testWhereSimple()
	{
		$expected = "SELECT * FROM table WHERE col = variable";

		$query = $this->_builder
			->select("*")
			->from('table')
			->where('col = variable')
			->getQueryString()
		;

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testWhereWithVariables()
	{
		$expected = "SELECT * FROM table WHERE col = 'string' AND col2 = 100 OR col3 = 'ORstring'";

		$query = $this->_builder
			->select("*")
			->from('table')
			->where('col = ?s', ["string"])
			->where('col2 = ?i', [100])
			->where('col3 = ?s', ['ORstring'], false)
			->getQueryString()
		;

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

}
