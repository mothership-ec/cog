<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for a single date.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Date extends Field
{
	public function getFieldType()
	{
		return 'date';
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
		$form->add($this->getName(), 'date', $this->getFieldOptions());
	}
}