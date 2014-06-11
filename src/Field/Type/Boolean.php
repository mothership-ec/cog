<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;


/**
 * A field for a boolean toggle.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Boolean extends Field
{
	public function getFieldType()
	{
		return 'boolean';
	}

	public function getFormType()
	{
		return 'checkbox';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'checkbox', $this->getFieldOptions());
	}
}