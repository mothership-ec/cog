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

	public function testSelectArray()
	{
		$query = $this->_builder
			->select(["col_1", "col_2", "col_3", "col_4"])
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

	public function testLimitToFrom()
	{
		$query = $this->_builder
			->select("*")
			->from('table')
			->limit(2, 5)
			->getQueryString()
		;

		$expected = "SELECT * FROM table LIMIT 2, 5";

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
		$result = m::mock('Message\Cog\DB\Adapter\ResultInterface');
		$result
			->shouldReceive('getAffectedRows')->once()
			->andReturn([])
			->shouldReceive('getLastInsertId')->once()
			->andReturn(1)
		;

		$this->_connect->shouldReceive('query')->once()
			->andReturn($result)
		;

		$this->_parser->shouldReceive('parse')->once()
			->passthru()
		;

		$query = $this->_builder
			->select("*")
			->from('table')
			->where('col = variable')
			->getQuery()
		;

		$query->run();
		$query = $query->getParsedQuery();

		$expected = "SELECT * FROM table WHERE col = variable";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testJoinSimple()
	{
		$query = $this->_builder
			->select("*")
			->from('table_a')
			->join('table_b', 'id = id')
			->getQueryString()
		;

		$expected = "SELECT * FROM table_a JOIN table_b ON id = id";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testJoinAlias()
	{
		$query = $this->_builder
			->select("*")
			->from('table_a')
			->join('alias', 'id = id', 'table_b')
			->getQueryString()
		;

		$expected = "SELECT * FROM table_a JOIN table_b alias ON id = id";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testLeftJoinSimple()
	{
		$query = $this->_builder
			->select("*")
			->from('table_a')
			->leftJoin('table_b', 'id = id')
			->getQueryString()
		;

		$expected = "SELECT * FROM table_a LEFT JOIN table_b ON id = id";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testLeftJoinAlias()
	{
		$query = $this->_builder
			->select("*")
			->from('table_a')
			->leftJoin('alias', 'id = id', 'table_b')
			->getQueryString()
		;

		$expected = "SELECT * FROM table_a LEFT JOIN table_b alias ON id = id";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testJoinQB()
	{
		$table_a = new QueryBuilder($this->_connect, $this->_parser);
		$table_a
			->select('*')
			->from('table_b')
		;

		$query =  $this->_builder
			->select("*")
			->from('table_a')
			->join('alias', 'id = id', $table_a)
			->getQueryString()
		;

		$expected = "SELECT * FROM table_a JOIN (SELECT * FROM table_b) alias ON id = id";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}

	public function testLeftJoinQB()
	{
		$table_a = new QueryBuilder($this->_connect, $this->_parser);
		$table_a
			->select('*')
			->from('table_b')
		;

		$query =  $this->_builder
			->select("*")
			->from('table_a')
			->leftJoin('alias', 'id = id', $table_a)
			->getQueryString()
		;

		$expected = "SELECT * FROM table_a LEFT JOIN (SELECT * FROM table_b) alias ON id = id";

		$this->assertEquals($expected, trim(preg_replace('/\s+/', ' ', $query)));
	}
	
	/**
     * @expectedException InvalidArgumentException
     */
	public function testNoFromError()
	{
		$query =  $this->_builder
			->select("*")
			->getQueryString()
		;
	}

	/**
     * @expectedException InvalidArgumentException
     */
	public function testNoSelectError()
	{
		$query =  $this->_builder
			->from("a")
			->getQueryString()
		;
	}

	public function testEleanorsQuery()
	{
		$forQuerys = [];
		$this->_parser->shouldReceive('parse')->zeroOrMoreTimes()->passthru();
		$q = new QueryBuilder($this->_connect, $this->_parser);
		$forQuerys[] = $q
			->select('item.created_at AS date')
			->select('(IFNULL(item.net, 0)) AS net')
			->select('(IFNULL(item.tax, 0)) AS tax')
			->select('(IFNULL(item.gross, 0)) AS gross')
			->select('CONCAT(order_summary.type," Sale") AS `type`')
			->select('item.item_id AS item_id')
			->select('item.order_id AS order_id')
			->select('item.product_name AS product')
			->select('item.options AS `option`')
			->from('order_item AS item')
			->join('order_summary', 'item.order_id = order_summary.order_id')
			->leftJoin('return_item', 'return_item.exchange_item_id = item.item_id')
			->where('order_summary.status_code >= 0')
			->where('item.product_id NOT IN (9)')
			->where('return_item.exchange_item_id IS NULL')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$q = new QueryBuilder($this->_connect, $this->_parser);
		$forQuerys[] = $q
			->select('order_summary.created_at AS date')
			->select('(IFNULL(net, 0)) AS net')
			->select('(IFNULL(tax, 0)) AS tax')
			->select('(IFNULL(gross, 0)) AS gross')
			->select('"Shipping In" AS `type`')
			->select('"" AS item_id')
			->select('order_shipping.order_id AS order_id')
			->select('"" AS product')
			->select('"" AS `option`')
			->from('order_shipping')
			->join('order_summary', 'order_shipping.order_id = order_summary.order_id')
			->where('order_summary.status_code >= 0')
			->where('order_summary.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$q = new QueryBuilder($this->_connect, $this->_parser);
		$forQuerys[] = $q
			->select('completed_at AS date')
			->select('(IFNULL(item.net, 0)) AS net')
			->select('(IFNULL(item.tax, 0)) AS tax')
			->select('(IFNULL(item.gross, 0)) AS gross')
			->select('"Exchange item" AS `type`')
			->select('item.item_id AS item_id')
			->select('item.order_id AS order_id')
			->select('item.product_name AS product')
			->select('item.options AS `option`')
			->from('order_item AS item')
			->join('order_summary', 'order_item.order_id = order_summary.order_id')
			->join('return_item', 'return_item.exchange_item_id = item.item_id')
			->join('ois', 'ois.item_id = item.item_id AND ois.status_code = 0', 'order_item_status')
			->where('order_summary.status_code >= 0')
			->where('item.product_id NOT IN (9)')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$q = new QueryBuilder($this->_connect, $this->_parser);
		$forQuerys[] = $q
			->select('item.completed_at AS date')
			->select('-(IFNULL(net, 0)) AS net')
			->select('-(IFNULL(tax, 0)) AS tax')
			->select('-(IFNULL(gross, 0)) AS gross')
			->select('"Return" AS `type`')
			->select('item.item_id AS item_id')
			->select('item.order_id AS order_id')
			->select('item.product_name AS product')
			->select('item.options AS `option`')
			->from('return_item AS item')
			->join('`return`', 'return_item.return_id = return.order_id')
			->where('accepted = 1')
			->where('status_code >= 2200')
			->where('item.product_id NOT IN (9)')
			->where('item.completed_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$fromQuery = new QueryBuilder($this->_connect, $this->_parser);
		foreach($forQuerys as $query) {
			$fromQuery->unionAll($query);
		}

		$query = new QueryBuilder($this->_connect, $this->_parser);
		$query
			->select('date AS date')
			->select('SUM(all.net) AS net')
			->select('SUM(all.tax) AS tax')
			->select('SUM(all.gross) AS gross')
			->select('all.type AS `type`')
			->select('all.order_id AS `order_id`')
			->select('all.item_id AS item_id')
			->select('all.product')
			->select('all.option')
			->select('country_id AS country')
			->from('all', $fromQuery)
		;

		$expected = 
'SELECT
date AS date,
SUM(all.net) AS net,
SUM(all.tax) AS tax,
SUM(all.gross) AS gross,
all.type AS `type`,
all.order_id AS `order_id`,
all.item_id AS item_id,
all.product,
all.option,
country_id AS country
FROM (SELECT
item.created_at AS date,
(IFNULL(item.net, 0)) AS net,
(IFNULL(item.tax, 0)) AS tax,
(IFNULL(item.gross, 0)) AS gross,
CONCAT(order_summary.type," Sale") AS `type`,
item.item_id AS item_id,
item.order_id AS order_id,
item.product_name AS product,
item.options AS `option`
FROM order_item AS item
JOIN order_summary ON item.order_id = order_summary.order_id
LEFT JOIN return_item ON return_item.exchange_item_id = item.item_id
WHERE
order_summary.status_code >= 0
AND item.product_id NOT IN (9)
AND return_item.exchange_item_id IS NULL
AND item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())
UNION ALL
SELECT
order_summary.created_at AS date,
(IFNULL(net, 0)) AS net,
(IFNULL(tax, 0)) AS tax,
(IFNULL(gross, 0)) AS gross,
"Shipping In" AS `type`,
"" AS item_id,
order_shipping.order_id AS order_id,
"" AS product,
"" AS `option`
FROM order_shipping
JOIN order_summary ON order_shipping.order_id = order_summary.order_id
WHERE
order_summary.status_code >= 0
AND order_summary.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())
UNION ALL
SELECT
completed_at AS date,
(IFNULL(item.net, 0)) AS net,
(IFNULL(item.tax, 0)) AS tax,
(IFNULL(item.gross, 0)) AS gross,
"Exchange item" AS `type`,
item.item_id AS item_id,
item.order_id AS order_id,
item.product_name AS product,
item.options AS `option`
FROM order_item AS item
JOIN order_summary ON order_item.order_id = order_summary.order_id
JOIN return_item ON return_item.exchange_item_id = item.item_id
JOIN order_item_status ois ON ois.item_id = item.item_id AND ois.status_code = 0
WHERE
order_summary.status_code >= 0
AND item.product_id NOT IN (9)
AND item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())
UNION ALL
SELECT
item.completed_at AS date,
-(IFNULL(net, 0)) AS net,
-(IFNULL(tax, 0)) AS tax,
-(IFNULL(gross, 0)) AS gross,
"Return" AS `type`,
item.item_id AS item_id,
item.order_id AS order_id,
item.product_name AS product,
item.options AS `option`
FROM return_item AS item
JOIN `return` ON return_item.return_id = return.order_id
WHERE
accepted = 1
AND status_code >= 2200
AND item.product_id NOT IN (9)
AND item.completed_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())) all';

		$this->assertEquals(trim(preg_replace('/\s+/', ' ', $expected)), trim(preg_replace('/\s+/', ' ', $query->getQueryString())));
	}
}