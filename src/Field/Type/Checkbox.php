<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;


/**
 * A field for a checkbox toggle.
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Checkbox extends Field
{
	public function getFieldType()
	{
		return 'checkbox';
	}

	public function getFormType()
	{
		return 'checkbox';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'checkbox', $this->getFieldOptions());
	}

	public function setValue($value)
	{
		$value = (int) $value;

		return parent::setValue($value);
	}

	public function getValue()
	{
		return (bool) parent::getValue();
	}
}