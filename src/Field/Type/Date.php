<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for a single date.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Date extends Datetime
{
	public function __toString()
	{
		return ($this->_value instanceof \DateTime) ? $this->_value->format('d m Y') : (string) $this->_value;
	}

	public function getFieldType()
	{
		return 'date';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'date', $this->getFieldOptions());
	}
}