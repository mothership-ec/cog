<?php

namespace Message\Cog\Test\DB\Adapter\MySQLi;

use Message\Cog\DB;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
	
	public function testCanConnect()
	{
		$connection = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
			'host'     => '127.0.0.1',
			'user'     => 'root',
			'password' => 'cheese',
			'db'	   => 'classicmodels',
			'lazy'	   => false,
		));
	}

	public function testCannotConnect()
	{
		$connection = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
			'host'     => '255.255.255.255',
			'user'     => 'invaliduser',
			'password' => 'badpass',
			'db'	   => 'thisdbdoesntexist',
			'lazy'	   => false,
		));
	}

	public function testQuery()
	{
		$connection = $this->_getConnection();

		// Connections are lazy so we need for force it.
		$connection->query("SELECT * FROM table LIMIT 10");
	}

	public function testEscape()
	{
		
	}

	public function testGettingLastError()
	{

	}

	public function testGettingAffectedRows()
	{

	}

	public function testGettingLastInsertId()
	{

	}

	protected function _getConnection()
	{
		return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
			'host'     => '127.0.0.1',
			'user'     => 'root',
			'password' => 'cheese',
			'db'	   => 'classicmodels',
		));

	}

}