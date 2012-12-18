<?php

namespace Message\Cog\Test\DB;


class ResultTest extends \PHPUnit_Framework_TestCase
{
	public function testIteratingAsArray()
	{
		
	}

	public function testGettingFirstRowFirstField()
	{
		
	}

	public function testGettingFirstRow()
	{
		
	}

	public function testHash()
	{
		
	}

	public function testTranspose()
	{

		$connection = new \Message\Cog\DB\Adapter\Faux\Connection;
		$connection->setResult(array(
			array(
				'forename' => 'James',
				'surname' => 'Moss',
				'age'	=> 24,
			),
			array(
				'forename' => 'Joe',
				'surname' => 'Holdcroft',
				'age'	=> 20,
			),
			array(
				'forename' => 'Danny',
				'surname' => 'Hannah',
				'age'	=> 25,
			),
		));

		$query = new \Message\Cog\DB\Query($connection);
		$result = $query->run("SELECT * FROM staff");

		$data = $result->transpose('');
	}

	public function testFlatten()
	{
		
	}

	public function testBind()
	{
		
	}

	public function testColumns()
	{
		
	}


}