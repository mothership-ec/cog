<?php

namespace Message\Cog\Test\Field;

use Message\Cog\Field\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
	public function testGetName()
	{
		$name  = 'my_special_field';
		$field = new FauxField;
		$field->setName($name);

		$this->assertSame($name, $field->getName());
	}
}