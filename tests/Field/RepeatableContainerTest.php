<?php

namespace Message\Cog\Test\Field;

use Message\Cog\Field\RepeatableContainer;

class RepeatableContainerTest extends \PHPUnit_Framework_TestCase
{

	public function testConstructorAndIteration()
	{
		$group = $this->_getGroup();

		$group->add($this->_getField());
		$group->add($this->_getMultipleValueField());

		$fields = new RepeatableContainer($group);
		$fields->add();

		foreach ($fields as $key => $fieldGroup) {
			$this->assertEquals($group, $fieldGroup);
		}

		return $fields;
	}

	/**
	 * @depends testConstructorAndIteration
	 */
	public function testCount($fields)
	{
		$this->assertSame(1, $fields->count());
		$this->assertSame(1, count($fields));
	}

	/**
	 * @depends testConstructorAndIteration
	 */
	public function testAddingAndGetting($fields)
	{
		$fields->add();
		$this->assertSame(2, count($fields));

		$fields->add();
		$this->assertSame(3, count($fields));

		$this->assertEquals($this->_getGroup(), $fields->get(2));
	}

	public function testGetIterator()
	{
		$group = $this->getMock('Message\Cog\Field\Group');
		$fields = new RepeatableContainer($group);

		$this->assertInstanceOf('\Traversable', $fields->getIterator());
	}

	private function _getGroup()
	{
		return $this->getMock('Message\Cog\Field\Group');
	}

	private function _getField()
	{
		return $this->getMockForAbstractClass('Message\Cog\Field\Field');
	}

	private function _getMultipleValueField()
	{
		return $this->getMockForAbstractClass('Message\Cog\Field\MultipleValueField');
	}
}