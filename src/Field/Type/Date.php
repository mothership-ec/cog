<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

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

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'date', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_getHelpKeys()),
		));
	}
}