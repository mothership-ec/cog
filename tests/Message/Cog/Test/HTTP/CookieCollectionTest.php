<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\CookieCollection;

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

		$this->assertEquals(count($pageTypes), $collection->count());

		foreach ($collection as $key => $pageType) {
			$this->assertEquals($pageTypes[$key], $pageType);
		}
	}

	public function testAdd()
	{
		$collection = new PageTypeCollection;
		$pageType   = new PageType\Blog;

		$this->assertEquals($collection, $collection->add($pageType));

		foreach ($collection as $type) {
			$this->assertEquals($pageType, $type);
		}
	}

	public function testCount()
	{
		$collection = new PageTypeCollection;

		$this->assertEquals(0, count($collection));
		$this->assertEquals(0, $collection->count());

		$collection->add(new PageType\Blog);

		$this->assertEquals(1, count($collection));
		$this->assertEquals(1, $collection->count());

		$collection->add(new PageType\Blog);

		$this->assertEquals(2, count($collection));
		$this->assertEquals(2, $collection->count());
	}

	public function testGetIterator()
	{
		$collection = new PageTypeCollection;
		$this->assertInstanceOf('\Iterator', $collection->getIterator());
	}
}