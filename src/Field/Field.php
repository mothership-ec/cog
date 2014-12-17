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
	 *
	 * @return Field
	 */
	public function setValue($value)
	{
		if (array_key_exists('data', $this->_options)) {
			unset($this->_options['data']);
		}

		$this->_value = $value;

		return $this;
	}
}