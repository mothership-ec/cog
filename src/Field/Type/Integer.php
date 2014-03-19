<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

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

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'number', $this->getLabel(), $this->getFieldOptions());
	}
}