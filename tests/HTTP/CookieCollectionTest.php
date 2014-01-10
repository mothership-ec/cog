<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\CookieCollection;
use Message\Cog\HTTP\Cookie;

class CookieCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorAndIteration()
	{
		$cookies = array(
			new Cookie('test1',11),
			new Cookie('test2',12),
			new Cookie('test3',13),
			new Cookie('test4',14),
			new Cookie('test5',15),
		);
		$collection = new CookieCollection($cookies);

		$this->assertEquals(count($cookies), $collection->count());

		foreach ($collection as $key => $cookie) {
			$this->assertEquals($cookies[$key], $cookie);
		}
	}

	public function testAdd()
	{
		$collection = new CookieCollection;
		$cookie = new Cookie('Hello','123hello');

		$this->assertEquals($collection, $collection->add($cookie));

		foreach ($collection as $c) {
			$this->assertEquals($cookie, $c);
		}
	}

	public function testCount()
	{
		$collection = new CookieCollection;

		$this->assertEquals(0, count($collection));
		$this->assertEquals(0, $collection->count());

		$collection->add(new Cookie('test',1));

		$this->assertEquals(1, count($collection));
		$this->assertEquals(1, $collection->count());

		$collection->add(new Cookie('test2',2));

		$this->assertEquals(2, count($collection));
		$this->assertEquals(2, $collection->count());
	}

	public function testGetIterator()
	{
		$collection = new CookieCollection;
		$this->assertInstanceOf('\Iterator', $collection->getIterator());
	}
}