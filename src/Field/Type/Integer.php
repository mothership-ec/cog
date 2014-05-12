<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for an integer.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Integer extends Field
{
	public function getFieldType()
	{
		return 'integer';
	}

	public function getFormType()
	{
		return 'number';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'number', $this->getFieldOptions());
	}
}