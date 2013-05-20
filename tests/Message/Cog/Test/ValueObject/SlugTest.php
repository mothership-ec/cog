<?php

namespace Message\Cog\Test\ValueObject;

use Message\Cog\ValueObject\Slug;

class SlugTest extends \PHPUnit_Framework_TestCase
{
	public function testIterationAndCountable()
	{
		$segments = array(
			'blogs',
			'my-category',
			'2013',
			'my-special-blog',
		);
		$slug = new Slug($segments);

		foreach ($slug as $key => $segment) {
			$this->assertEquals($segments[$key], $segment);
		}

		$this->assertInstanceOf('\Traversable', $slug->getIterator());

		$this->assertEquals(4, count($slug));
		$this->assertEquals(4, $slug->count());
	}

	public function testGetFull()
	{
		$segments = array(
			'products',
			'hats-and-shoes',
			'deals',
			'old-hat',
		);
		$full = 'products/hats-and-shoes/deals/old-hat';
		$slug = new Slug($segments);

		$this->assertEquals($full, $slug->getFull());
		$this->expectOutputString($full);

		echo $slug;
	}

	public function testSegmentsPassedAsString()
	{
		$segments = array(
			'secret',
			'place',
			'in',
			'the',
			'website',
		);
		$fullSlug = 'secret/place/in/the/website';
		$slug     = new Slug($fullSlug);

		$this->assertEquals(count($segments), count($slug));

		foreach ($slug as $key => $segment) {
			$this->assertEquals($segments[$key], $segment);
		}
	}
}