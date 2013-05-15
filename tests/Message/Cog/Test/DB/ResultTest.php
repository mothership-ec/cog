<?php

namespace Message\Cog\Test\DB;


class ResultTest extends \PHPUnit_Framework_TestCase
{
	public function testIteratingAsArray()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->value();

		// This test is kinda lame
		foreach($result as $row) {
			$this->assertInstanceOf('stdClass', $row);
		}
	}

	public function testGettingFirstRowFirstField()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->value();

		$this->assertEquals('James', $data);

		$result->reset();
		$this->assertEquals('James', $result[0]->forename);
	}

	public function testGettingFirstRow()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->row();

		$this->assertEquals((object)array(
			'forename' => 'James',
			'surname'  => 'Moss',
			'age'	   => 24,
		), $data);
	}

	public function testHash()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->hash('surname', 'age');

		$this->assertEquals(array(
			'Moss' => 24,
			'Holdcroft' => 20,
			'Hannah' => 25,
			'Bloggs' => 37,
		), $data);
	}

	public function testCollect()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->hash('surname', 'age');

		$this->assertEquals(array(
			'Moss'      => 24,
			'Holdcroft' => 20,
			'Hannah'    => 25,
			'Bloggs'    => 37,
		), $data);
	}

	public function testTranspose()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");

		$data = $result->transpose('forename');
		$this->assertEquals(array(
			'James' => (object)array(
				'forename' => 'James',
				'surname' => 'Moss',
				'age'	=> 24,
			),
			'Joe' => (object)array(
				'forename' => 'Joe',
				'surname' => 'Bloggs',
				'age'	=> 37,
			),
			'Danny' => (object)array(
				'forename' => 'Danny',
				'surname' => 'Hannah',
				'age'	=> 25,
			),
		), $data);
	}

	public function testFlatten()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->flatten('surname');

		$this->assertEquals(array(
			'Moss',
			'Holdcroft',
			'Hannah',
			'Bloggs',
		), $data);
	}

	public function testBind()
	{
		$obj =  new BindClass;

		$result = $this->getQuery()->run("SELECT * FROM staff");
		$obj = $result->bind($obj);

		$obj2 = new BindClass;
		$obj2->forename = 'James';
		$obj2->surname = 'Moss';
		$obj2->age = 24;

		$this->assertEquals($obj2, $obj);
	}

	public function testBindTo()
	{
		$className = 'Message\Cog\Test\DB\BindClass';

		$result = $this->getQuery()->run("SELECT * FROM staff");
		$obj = $result->bindTo($className);

		$this->assertInstanceOf($className, $obj);
	}

	public function testColumns()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->columns();

		$this->assertEquals(array(
			'forename',
			'surname',
			'age',
		), $data);
	}

	public function getQuery()
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
			array(
				'forename' => 'Joe',
				'surname' => 'Bloggs',
				'age'	=> 37,
			),
		));

		$query = new \Message\Cog\DB\Query($connection);

		return $query;
	}

}