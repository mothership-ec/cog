<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for some raw HTML.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Html extends Field
{
	public function getFieldType()
	{
		return 'html';
	}

	public function getFormType()
	{
		return 'textarea';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'textarea', $this->getFieldOptions());
	}
}