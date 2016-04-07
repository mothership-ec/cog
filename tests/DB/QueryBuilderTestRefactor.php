<?php

namespace Message\Cog\Test\DB;

use Message\Cog\DB\QueryBuilder;
use Mockery as m;

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

}
