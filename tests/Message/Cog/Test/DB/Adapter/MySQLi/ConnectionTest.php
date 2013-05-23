<?php

namespace Message\Cog\Test\DB\Adapter\MySQLi;

use Message\Cog\DB;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		if(!$this->isOnOfficeNetwork()) {
			$this->markTestSkipped(
				'Must be in the Message office to test MySQLi Adapter'
			);
		}
	}

	public function testCanConnect()
	{
		$connection = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
			'host'     => '192.168.201.99',
			'user'     => 'joe',
			'password' => 'cheese',
			'db'	   => 'message',
			'lazy'	   => false,
		));
	}

	/**
	* @expectedException PHPUnit_Framework_Error
	*/
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

	public function isOnOfficeNetwork()
	{
		// For the moment dont run these tests
		return false;

		static $result;

		if(!isset($result)) {
			$output = shell_exec('ping -c1 -t1 192.168.201.99');

			$result =  strpos($output, ' 0.0% packet loss') !== false;	
		}
		
		return $result;
	}

}