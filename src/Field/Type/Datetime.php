<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for a single date & time.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Datetime extends Field
{
	public function __toString()
	{
		return $this->_value;
	}

	public function getFieldType()
	{
		return 'datetime';
	}

	public function getValue()
	{
		if ($this->_value instanceof \DateTime) {
			return $this->_value;
		}

		return ($this->_value) ? new \DateTime(date('c', $this->_value)) : null;
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'datetime', $this->getFieldOptions());
	}
}