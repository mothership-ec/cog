<?php

namespace Message\Cog\Test\DB;

use Mockery as m;

class QueryParserTest extends \PHPUnit_Framework_TestCase
{
	private $_parser;
	private $_connection;

	public function setUp()
	{
		$connection       = m::mock('\Message\Cog\DB\Adapter\MySQLi\Connection');
		$this->_connection = $connection;
		$this->_parser    = new \Message\Cog\DB\QueryParser($connection);
	}

	public function testBasicParse()
	{
		$string = "SELECT * FROM `table`";
		$return = $this->_parser->parse("SELECT * FROM `table`", []);
		$this->assertEquals($string, $return);

		$string = "SELECT * FROM `table` WHERE `col`=?s"; $vars = ["string"];

		$this->_connection->shouldReceive('escape')->once()
			->with("string")
			->andReturn("string");
		$return = $this->_parser->parse($string, $vars);
		$this->assertEquals("SELECT * FROM `table` WHERE `col`='string'", $return);
	}

	public function testParseTypes()
	{
		$test = "SELECT * FROM `table` WHERE `int`=?i"; $val = 12;
		$this->_connection->shouldReceive('escape')->once()
			->with("12")
			->andReturn("12");
		$return = $this->_parser->parse($test, [$val]);
		$this->assertEquals("SELECT * FROM `table` WHERE `int`=12", $return);


		$test = "SELECT * FROM `table` WHERE `string`=?s"; $val = 'string';
		$this->_connection->shouldReceive('escape')->once()
			->with("string")
			->andReturn("string");
		$return = $this->_parser->parse($test, [$val]);
		$this->assertEquals("SELECT * FROM `table` WHERE `string`='string'", $return);

		$test = "SELECT * FROM `table` WHERE `string` IN (?js)"; $val = ['a', 'a', 'a'];
		$this->_connection->shouldReceive('escape')->times(3)
			->with("a")
			->andReturn("a");
		$return = $this->_parser->parse($test, [$val]);
		$this->assertEquals("SELECT * FROM `table` WHERE `string` IN ('a', 'a', 'a')", $return);

		$test = "SELECT * FROM `table` WHERE `string` IN (?ji)"; $val = [1, 1, 1];
		$this->_connection->shouldReceive('escape')->times(3)
			->with(1)
			->andReturn(1);
		$return = $this->_parser->parse($test, [$val]);
		$this->assertEquals("SELECT * FROM `table` WHERE `string` IN (1, 1, 1)", $return);

		// True
		$test = 'boolean test ?b'; $val = true;
		$this->_connection->shouldReceive('escape')->once()
			->andReturn(1);
		$return = $this->_parser->parse($test, [$val]);
		$this->assertEquals("boolean test 1", $return);

		// False
		$test = 'boolean test ?b'; $val = false;
		$this->_connection->shouldReceive('escape')->once()
			->andReturn(1);
		$return = $this->_parser->parse($test, [$val]);
		$this->assertEquals("boolean test 0", $return);
	}

}