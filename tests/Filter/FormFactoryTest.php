<?php

namespace Message\Cog\Test\Filter;

use Message\Cog\Filter\FormFactory;

/**
 * Class FormFactoryTest
 * @package Message\Cog\Test\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class FormFactoryTest extends \PHPUnit_Framework_TestCase
{
	const EXPECTED_NAME = 'filter_form';

	private $_filters;
	private $_factory;

	/**
	 * Create a mock of the filter collection, and a new instance of FormFactory
	 */
	protected function setUp()
	{
		$this->_filters = $this->getMockBuilder('Message\\Cog\\Filter\\FilterCollection')
			->getMock();

		$this->_factory = new FormFactory;
	}

	/**
	 * Test that getForm() returns a form instance with the default name when that variable is not set
	 */
	public function testGetFormNoName()
	{
		$this->_filters
			->expects($this->once())
			->method('count')
			->willReturn(1)
		;

		$form = $this->_factory->getForm($this->_filters);

		$this->assertInstanceOf('Message\\Cog\\Filter\\FilterForm', $form);
		$this->assertSame(self::EXPECTED_NAME, $form->getName());
	}

	/**
	 * Test that getForm() returns a form instance with the given name
	 */
	public function testGetFormWithName()
	{
		$this->_filters
			->expects($this->once())
			->method('count')
			->willReturn(1)
		;

		$name = 'name';

		$form = $this->_factory->getForm($this->_filters, $name);

		$this->assertInstanceOf('Message\\Cog\\Filter\\FilterForm', $form);
		$this->assertSame($name, $form->getName());
	}

	/**
	 * Test that an exception is thrown when no filters are present in the collection
	 *
	 * @expectedException \Message\Cog\Filter\Exception\NoFiltersException
	 */
	public function testGetFormNoFilters()
	{
		$this->_filters
			->expects($this->once())
			->method('count')
			->willReturn(0)
		;

		$this->_factory->getForm($this->_filters);
	}
}