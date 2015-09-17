<?php

namespace Message\Cog\Test\Field;

use Message\Cog\Field\Field;

class FauxField extends Field
{
	public function getFieldType()
	{
		return 'text';
	}
}