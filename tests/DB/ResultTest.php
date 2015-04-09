<?php

namespace Message\Cog\Test\DB;


class ResultTest extends \PHPUnit_Framework_TestCase
{
	public function testIteratingAsArray()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->value();

		foreach($result as $row) {
			$this->assertInstanceOf('stdClass', $row);
		}
	}

	public function testArrayAccessor()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$this->assertEquals('James', $result[0]->forename);
		$this->assertNull($result[123465]);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testArraySet()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$result[10] = 'You cant do this';
	}

	/**
	 * @expectedException \Exception
	 */
	public function testArrayUnset()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		unset($result[0]);
	}

	public function testArrayIsset()
	{
		
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$this->assertTrue(isset($result[2]));
		$this->assertFalse(isset($result[5]));
	}

	public function testArrayCount()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");

		$this->assertEquals(4, count($result));
	}

	public function testArrayKey()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$this->assertEquals(0, $result->key());

		foreach($result as $row) {
			// testing iterating
		}

		$this->assertEquals(4, $result->key());
	}

	public function testGettingFirstRowFirstField()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->value();

		$this->assertEquals('James', $data);
		
	}

	public function testGettingFirstRow()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->first();

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
			'Moss'      => 24,
			'Holdcroft' => 20,
			'Hannah'    => 25,
			'Bloggs'    => 37,
		), $data);
	}

	public function testHashWithAutoColumns()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->hash();

		$this->assertEquals(array(
			'James' => 'Moss',
			'Joe'   => 'Bloggs',
			'Danny' => 'Hannah',
		), $data);
	}

	public function testCollect()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$data = $result->collect('forename');

		$this->assertEquals(
			array(
				'James' => array(
					(object)array(
						'forename' => 'James',
						'surname' => 'Moss',
						'age'	=> 24,
					),
				),
				'Joe' => array(
					(object)array(
						'forename' => 'Joe',
						'surname' => 'Holdcroft',
						'age'	=> 20,
					),
					(object)array(
						'forename' => 'Joe',
						'surname' => 'Bloggs',
						'age'	=> 37,
					),
				),
				'Danny' => array(
					(object)array(
						'forename' => 'Danny',
						'surname' => 'Hannah',
						'age'	=> 25,
					),
				),

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
		$obj2->surname  = 'Moss';

		$this->assertEquals($obj2, $obj);
	}

	public function testBindWithForce()
	{
		$obj =  new BindClass;

		$result = $this->getQuery()->run("SELECT * FROM staff");
		$obj = $result->bind($obj, true);

		$obj2 = new BindClass;
		$obj2->forename = 'James';
		$obj2->surname  = 'Moss';
		$obj2->age      = 24;

		$this->assertEquals($obj2, $obj);
	}

	public function testBindWithArray()
	{
		$classes =  array(
			new BindClass,
			new BindClass,
			new BindClass,
			new BindClass,
		);

		$result = $this->getQuery()->run("SELECT * FROM staff");
		$classes = $result->bind($classes);

		$testClasses = array();

		$obj = new BindClass;
		$obj->forename = 'James';
		$obj->surname = 'Moss';
		$testClasses[] = $obj;

		$obj = new BindClass;
		$obj->forename = 'Joe';
		$obj->surname = 'Holdcroft';
		$testClasses[] = $obj;

		$obj = new BindClass;
		$obj->forename = 'Danny';
		$obj->surname = 'Hannah';
		$testClasses[] = $obj;

		$obj = new BindClass;
		$obj->forename = 'Joe';
		$obj->surname = 'Bloggs';
		$testClasses[] = $obj;

		$this->assertEquals($testClasses, $classes);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBindWithString()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$result->bind('Derp');
	}

	public function testBindTo()
	{
		$className = 'Message\Cog\Test\DB\BindClass';

		$result = $this->getQuery()->run("SELECT * FROM staff");
		$result = $result->bindTo($className);

		$testClasses = array();

		$obj = new BindClass;
		$obj->forename = 'James';
		$obj->surname = 'Moss';
		$testClasses[] = $obj;

		$obj = new BindClass;
		$obj->forename = 'Joe';
		$obj->surname = 'Holdcroft';
		$testClasses[] = $obj;

		$obj = new BindClass;
		$obj->forename = 'Danny';
		$obj->surname = 'Hannah';
		$testClasses[] = $obj;

		$obj = new BindClass;
		$obj->forename = 'Joe';
		$obj->surname = 'Bloggs';
		$testClasses[] = $obj;

		$this->assertEquals($testClasses, $result);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBindToWithObject()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$obj = $result->bindTo(new \stdClass);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testBindToWithBadClass()
	{
		$result = $this->getQuery()->run("SELECT * FROM staff");
		$obj = $result->bindTo('FLOBITYBOBITY}{}}}');
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

	public function testId()
	{
		$connection = new \Message\Cog\DB\Adapter\Faux\Connection(array(
			'insertId' => 1337,
		));
		$connection->setResult(array(
			array(
				'forename' => 'James',
				'surname' => 'Moss',
				'age'	=> 24,
			),
		));

		$parser = $this->getMockBuilder('Message\\Cog\\DB\\QueryParser')->disableOriginalConstructor()->getMock();
		$query = new \Message\Cog\DB\Query($connection, $parser);
		$result = $query->run("SELECT * FROM staff");

		$this->assertEquals(1337, $result->id());
	}

	public function testAffectedRows()
	{
		$connection = new \Message\Cog\DB\Adapter\Faux\Connection(array(
			'affectedRows' => 2345,
		));
		$connection->setResult(array(
			array(
				'forename' => 'James',
				'surname' => 'Moss',
				'age'	=> 24,
			),
		));

		$parser = $this->getMockBuilder('Message\\Cog\\DB\\QueryParser')->disableOriginalConstructor()->getMock();
		$query = new \Message\Cog\DB\Query($connection, $parser);
		$result = $query->run("SELECT * FROM staff");

		$this->assertEquals(2345, $result->affected());
	}

	public function testIsFromTransaction()
	{
		$connection = new \Message\Cog\DB\Adapter\Faux\Connection;
		$connection->setResult(array(
			array(
				'forename' => 'James',
				'surname' => 'Moss',
				'age'	=> 24,
			),
		));

		$parser = $this->getMockBuilder('Message\\Cog\\DB\\QueryParser')->disableOriginalConstructor()->getMock();
		$dispatcher = $this->getMockBuilder('Message\\Cog\\Event\\Dispatcher')->disableOriginalConstructor()->getMock();

		$query = new \Message\Cog\DB\Transaction($connection, $parser, $dispatcher);
		$result = $query->add("SELECT * FROM staff")->commit();

		$this->assertTrue($result->isFromTransaction());
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

		$parser = $this->getMockBuilder('Message\\Cog\\DB\\QueryParser')->disableOriginalConstructor()->getMock();

		$query = new \Message\Cog\DB\Query($connection, $parser);

		return $query;
	}

}