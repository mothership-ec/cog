<?php

namespace Message\Cog\Test\Filter;

use Message\Cog\Filter\FilterForm;

/**
 * Class FilterFormTest
 * @package Message\Cog\Test\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class FilterFormTest extends \PHPUnit_Framework_TestCase
{
	const NAME = 'name';
	const FILTER_NAME_1 = 'name_1';
	const FILTER_NAME_2 = 'name_2';

	private $_filter1;
	private $_filter2;
	private $_filters1;
	private $_filters2;
	private $_formBuilder;

	/**
	 * {@inheritDoc}
	 *
	 * Create mocks for the following:
	 *     - The Symfony form builder
	 *     - Two different filters
	 *     - A filter collection with one filter
	 *     - A filter collection with two filters
	 */
	protected function setUp()
	{
		$this->_filter1 = $this->getMockBuilder('Message\\Cog\\Test\\Filter\\FauxFilter')
			->setConstructorArgs([self::FILTER_NAME_1, self::FILTER_NAME_1])
			->setMethods(['getName', 'getForm', 'getOptions'])
			->getMock()
		;

		$this->_filter2 = $this->getMockBuilder('Message\\Cog\\Test\\Filter\\FauxFilter')
			->setConstructorArgs([self::FILTER_NAME_2, self::FILTER_NAME_2])
			->setMethods(['getName', 'getForm', 'getOptions'])
			->getMock()
		;

		$this->_filters1 = $this->getMockBuilder('Message\\Cog\\Filter\\FilterCollection')
			->setConstructorArgs([[$this->_filter1]])
			->setMethods(['setKey', 'addValidator'])
			->getMock()
		;

		$this->_filters2 = $this->getMockBuilder('Message\\Cog\\Filter\\FilterCollection')
			->setConstructorArgs([[$this->_filter1, $this->_filter2]])
			->setMethods(['setKey', 'addValidator'])
			->getMock()
		;

		$this->_formBuilder = $this->getMockBuilder('Symfony\\Component\\Form\\FormBuilder')
			->disableOriginalConstructor()
			->setMethods(['add'])
			->getMock()
		;

	}

	/**
	 * Test that an exception is thrown if the name given to the constructor is not a string
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testConstructNonStringName()
	{
		$form = new FilterForm(new \stdClass, $this->_filters1);
	}

	/**
	 * Test that the name given in the constructor matches that returned by getName()
	 */
	public function testGetName()
	{
		$this->assertSame(self::NAME, $this->_getFilterForm()->getName());
	}

	/**
	 * Test that the Symfony form builder will call the appropriate methods when there is one
	 * filter in the collection
	 */
	public function testBuildFormOneFilter()
	{
		$form = $this->_getFilterForm();

		$this->_formBuilder
			->expects($this->once())
			->method('add')
		;

		$this->_filter1
			->expects($this->once())
			->method('getName')
			->willReturn(self::FILTER_NAME_1)
		;

		$this->_filter1
			->expects($this->once())
			->method('getForm')
			->willReturn('choice')
		;

		$this->_filter1
			->expects($this->once())
			->method('getOptions')
			->willReturn([])
		;

		$form->buildForm($this->_formBuilder, []);

	}

	/**
	 * Test that the Symfony form builder will call the appropriate methods when there are multiple
	 * filters in the collection
	 */
	public function testBuildFormMultipleFilters()
	{
		$form = new FilterForm(self::NAME, $this->_filters2);

		$this->_formBuilder
			->expects($this->exactly(2))
			->method('add')
		;

		$this->_filter1
			->expects($this->once())
			->method('getName')
			->willReturn(self::FILTER_NAME_1)
		;

		$this->_filter1
			->expects($this->once())
			->method('getForm')
			->willReturn('choice')
		;

		$this->_filter1
			->expects($this->once())
			->method('getOptions')
			->willReturn([])
		;

		$this->_filter2
			->expects($this->once())
			->method('getName')
			->willReturn(self::FILTER_NAME_2)
		;

		$this->_filter2
			->expects($this->once())
			->method('getForm')
			->willReturn('choice')
		;

		$this->_filter2
			->expects($this->once())
			->method('getOptions')
			->willReturn([])
		;

		$form->buildForm($this->_formBuilder, []);
	}

	/**
	 * Get an instance of the filter form with the first filter collection mock
	 *
	 * @return FilterForm
	 */
	private function _getFilterForm()
	{
		return new FilterForm(self::NAME, $this->_filters1);
	}
}