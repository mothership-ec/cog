<?php

namespace Message\Cog\Test\Filter;

/**
 * Class AbstractFilterTest
 * @package Message\Cog\Test\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Test for AbstractFilter, uses the FauxFilter class
 */
class AbstractFilterTest extends \PHPUnit_Framework_TestCase
{
	const NAME = 'name';
	const DISPLAY_NAME = 'display_name';

	/**
	 * Test that getName() returns the name passed into the constructor
	 */
	public function testGetName()
	{
		$filter = new FauxFilter(self::NAME, self::DISPLAY_NAME);
		$this->assertSame(self::NAME, $filter->getName());
	}

	/**
	 * Test that an exception is thrown if a non-string is given as a name to the constructor
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetNameThrowException()
	{
		$filter = new FauxFilter(new \stdClass, self::DISPLAY_NAME);
	}

	/**
	 * Test that getDisplayName() returns the display name passed into the constructor
	 */
	public function testGetDisplayName()
	{
		$filter = new FauxFilter(self::NAME, self::DISPLAY_NAME);
		$this->assertSame(self::DISPLAY_NAME, $filter->getDisplayName());
	}

	/**
	 * Test that an exception is thrown if a non-string is given as the display name to
	 * the constructor
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testSetDisplayNameThrowException()
	{
		$filter = new FauxFilter(self::NAME, new \stdClass);
	}

	/**
	 * Test that getForm() returns 'choice' by default
	 */
	public function testGetForm()
	{
		$this->assertSame('choice', $this->_getFilter()->getForm());
	}

	/**
	 * Test that the options required for a checkbox are returned, with the label matching
	 * the display name
	 */
	public function testGetOptionsNoOverride()
	{
		$expected = [
			'multiple' => true,
			'expanded' => true,
			'label'    => self::DISPLAY_NAME,
		];

		$this->assertSame($expected, $this->_getFilter()->getOptions());
	}

	/**
	 * Test that you can override options completely
	 */
	public function testSetOptionsCompleteOverride()
	{
		$options = [
			'multiple' => false,
			'expanded' => false,
			'label' => 'this is the new label',
		];

		$filter = $this->_getFilter();
		$filter->setOptions($options);

		// Use assertEquals as order may vary
		$this->assertEquals($options, $filter->getOptions());
	}

	/**
	 * Test that you can override some options while keeping others intact
	 */
	public function testSetOptionsPartialOverride()
	{
		$newLabel = 'new label';
		$options = [
			'label' => $newLabel,
		];

		$expected = [
			'multiple' => true,
			'expanded' => true,
			'label' => $newLabel,
		];

		$filter = $this->_getFilter();
		$filter->setOptions($options);

		// Use assertEquals as order may vary
		$this->assertEquals($expected, $filter->getOptions());
	}

	/**
	 * Test that you can add new options while keeping existing options intact
	 */
	public function testSetOptionsAddNewOption()
	{
		$newValue = 'new value';
		$newKey = 'new key';

		$options = [
			$newKey => $newValue,
		];

		$expected = [
			'multiple' => true,
			'expanded' => true,
			'label' => self::DISPLAY_NAME,
			$newKey => $newValue,
		];

		$filter = $this->_getFilter();
		$filter->setOptions($options);

		// Use assertEquals as order may vary
		$this->assertEquals($expected, $filter->getOptions());
	}

	/**
	 * Test that you cannot apply the filter without setting a value first
	 *
	 * @expectedException \Message\Cog\Filter\Exception\NoValueSetException
	 */
	public function testApplyNoValueSet()
	{
		$queryBuilder = $this->_getQueryBuilder();

		$this->_getFilter()->apply($queryBuilder);
	}

	/**
	 * Get a basic instance of the filter
	 *
	 * @return FauxFilter
	 */
	private function _getFilter()
	{
		return new FauxFilter(self::NAME, self::DISPLAY_NAME);
	}

	/**
	 * Get a mock of the QueryBuilder
	 *
	 * @return \Message\Cog\DB\QueryBuilder
	 */
	private function _getQueryBuilder()
	{
		return $this->getMockBuilder('Message\\Cog\\DB\\QueryBuilder')->disableOriginalConstructor()->getMock();
	}
}