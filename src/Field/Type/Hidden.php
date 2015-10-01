<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A hidden field
 *
 * @author Samuel Trangmat-Keates <sam@message.co.uk>
 */
class Hidden extends Field
{
	public function getFieldType()
	{
		return 'hidden';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'hidden', $this->getFieldOptions());
	}
}