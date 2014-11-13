<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;


/**
 * Alias for checkbox, preserved for BC
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Boolean extends Checkbox
{
	/**
	 * @deprecated
	 * @return string
	 */
	public function getFieldType()
	{
		return 'boolean';
	}

}