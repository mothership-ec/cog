<?php

namespace Message\Cog\Test\Field;

use Message\Cog\Field\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
	static public function getFalseyValues()
	{
		return array(
			array(''),
			array(false),
			array(0),
			array(null),
			array(0.000),
		);
	}

	public function testGettingSetting()
	{
		$group = new Group;
		$field = new FauxField;
		$field->setName('my_field');

		$group->add($field);

		$this->assertSame($field, $group->my_field);

		return $group;
	}

	/**
	 * @depends testGettingSetting
	 */
	public function testIsset(Group $group)
	{
		$this->assertTrue(isset($group->my_field));
		$this->assertFalse(isset($group->thisIsNotSet));
	}

	/**
	 * @expectedException        \OutOfBoundsException
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingUndefinedField()
	{
		$group = new Group('group2');

		$group->iDunnoSomeField;
	}

	public function testRepeatable()
	{
		$group = new Group('group');

		$this->assertFalse($group->isRepeatable());
		$this->assertSame($group, $group->setRepeatable(true));
		$this->assertTrue($group->isRepeatable());
		$this->assertSame($group, $group->setRepeatable(false));
		$this->assertFalse($group->isRepeatable());
	}

	public function testIdentifierField()
	{
		$group = new Group;
		$field = new FauxField;
		$field->setName('description');

		$this->assertFalse($group->getIdentifierField());

		// Test automatic setting of field doesn't happen for a non-titley field
		$group->add($field);

		$this->assertFalse($group->getIdentifierField());

		$this->assertSame($group, $group->setIdentifierField('description'));
		$this->assertSame($field, $group->getIdentifierField());
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage does not exist
	 */
	public function testSetUnknownIdentifierField()
	{
		$group = new Group;

		$group->setIdentifierField('dunno_what_this_is');
	}

	public function testIdentifierFieldNotSetAutomaticallyWhenAlreadySet()
	{
		$group = new Group;

		$field = new FauxField;
		$field->setName('my_field');

		$wrongField = new FauxField;
		$wrongField->setName('wrong_field');

		$group
			->add($field)
			->setIdentifierField('my_field');

		$group->add($wrongField);

		$this->assertSame($field, $group->getIdentifierField());
	}
}