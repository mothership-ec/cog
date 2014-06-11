<?php

namespace Message\Cog\Field;

/**
 * Represents a page content field.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class Field extends BaseField
{
	/**
	 * Set the value for this field.
	 *
	 * @param mixed $value The field value
	 */
	public function setValue($value)
	{
		$this->_value = $value;

		return $this;
	}
}