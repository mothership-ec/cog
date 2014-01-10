<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests whether the constructor works properly
	 */
	public function testConstruct()
	{
		$field1 = new Field('name', 'Readable Name');
		$field2 = new Field('weirdName_right');

		$this->assertEquals('Readable Name', $field1->readableName);
		$this->assertEquals('name', $field1->name);

		$this->assertEquals('Weird Name Right', $field2->readableName);
		$this->assertEquals('weirdName_right',  $field2->name);
	}

}