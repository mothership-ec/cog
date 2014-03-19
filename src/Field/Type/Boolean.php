<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

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

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'checkbox', $this->getLabel(), $this->getFieldOptions());
	}
}