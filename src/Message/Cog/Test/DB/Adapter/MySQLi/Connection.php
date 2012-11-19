<?php

namespace Message\Cog\DB\Test\Adapter\MySQLi;

use Message\Cog\DB;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
	
	public function testCanConnect()
	{
		$connection = new DB\Adapter\MySQLi\Connection(array(
			'user' => 'test',
			''
		));
	}

	public function testQuery()
	{

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

}