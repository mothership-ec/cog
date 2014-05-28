<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for plain text.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Text extends Field
{
	public function getFieldType()
	{
		return 'text';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'text', $this->getFieldOptions());
	}
}