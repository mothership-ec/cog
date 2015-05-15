<?php

namespace Message\Cog\Test\Filter;

use Message\Cog\Filter\FilterCollection;

/**
 * Class FilterCollectionTest
 * @package Message\Cog\Test\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class FilterCollectionTest extends \PHPUnit_Framework_TestCase
{
	const NAME_1 = 'name_1';
	const NAME_2 = 'name_2';

	/**
	 * Test that the constructor works and adds the filters properly
	 */
	public function testConstructValid()
	{
		$filters = new FilterCollection([
			new FauxFilter(self::NAME_1, self::NAME_1),
			new FauxFilter(self::NAME_2, self::NAME_2)
		]);

		$this->assertTrue($filters->count() === 2);
	}

	/**
	 * Test that you cannot add a non-filter to the collection via the constructor
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructInvalid()
	{
		$filters = new FilterCollection([
			new \stdClass
		]);
	}

	/**
	 * Test that you cannot add duplicate filters to the collection via the constructor
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructInvalidDuplicate()
	{
		$filters = new FilterCollection([
			new FauxFilter(self::NAME_1, self::NAME_1),
			new FauxFilter(self::NAME_1, self::NAME_1)
		]);
	}

	/**
	 * Test that you can add filters to the collection via the add() method
	 */
	public function testAdd()
	{
		$filters = new FilterCollection;

		$filters->add(new FauxFilter(self::NAME_1, self::NAME_1));
		$this->assertTrue($filters->count() === 1);

		$filters->add(new FauxFilter(self::NAME_2, self::NAME_2));
		$this->assertTrue($filters->count() === 2);
	}

	/**
	 * Test that you cannot add non-filters to the collection via the add() method
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddInvalid()
	{
		$filters = new FilterCollection;

		$filters->add(new \stdClass);
	}

	/**
	 * Test that you cannot add duplicate filters to the collection via the add() method
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddInvalidDuplicate()
	{
		$filters = new FilterCollection;

		$filters->add(new FauxFilter(self::NAME_1, self::NAME_1));
		$filters->add(new FauxFilter(self::NAME_1, self::NAME_1));
	}

	/**
	 * Test that the keys for filters are set to the name of the filter
	 */
	public function testKey()
	{
		$filters = new FilterCollection([
			new FauxFilter(self::NAME_1, self::NAME_1),
		]);

		foreach ($filters as $key => $value) {
			$this->assertSame(self::NAME_1, $key);
		}
	}
}