<?php

namespace Message\Cog\Test\Filter;

class AbstractFilterTest extends \PHPUnit_Framework_TestCase
{
	const NAME = 'name';
	const DISPLAY_NAME = 'display_name';

	public function testGetName()
	{
		$filter = new FauxFilter(self::NAME, self::DISPLAY_NAME);
		$this->assertSame(self::NAME, $filter->getName());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetNameThrowException()
	{
		$filter = new FauxFilter(new \stdClass, self::DISPLAY_NAME);
	}

	public function testGetDisplayName()
	{
		$filter = new FauxFilter(self::NAME, self::DISPLAY_NAME);
		$this->assertSame(self::DISPLAY_NAME, $filter->getDisplayName());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetDisplayNameThrowException()
	{
		$filter = new FauxFilter(self::NAME, new \stdClass);
	}

	public function testGetForm()
	{
		$this->assertSame('choice', $this->_getFilter()->getForm());
	}

	public function testGetOptionsNoOverride()
	{
		$expected = [
			'multiple' => true,
			'expanded' => true,
			'label'    => self::DISPLAY_NAME,
		];

		$this->assertSame($expected, $this->_getFilter()->getOptions());
	}

	public function testSetOptionsCompleteOverride()
	{
		$options = [
			'multiple' => false,
			'expanded' => false,
			'label' => 'this is the new label',
		];

		$filter = $this->_getFilter();
		$filter->setOptions($options);

		$this->assertSame($options, $filter->getOptions());
	}

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

		$this->assertEquals($expected, $filter->getOptions());
	}

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

		$this->assertEquals($expected, $filter->getOptions());
	}

	/**
	 * @expectedException \Message\Cog\Filter\Exception\NoValueSetException
	 */
	public function testApplyNoValueSet()
	{
		$queryBuilder = $this->_getQueryBuilder();

		$this->_getFilter()->apply($queryBuilder);
	}

	private function _getFilter()
	{
		return new FauxFilter(self::NAME, self::DISPLAY_NAME);
	}

	private function _getQueryBuilder()
	{
		return $this->getMockBuilder('Message\\Cog\\DB\\QueryBuilder')->disableOriginalConstructor()->getMock();
	}
}