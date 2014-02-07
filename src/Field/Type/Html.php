<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

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

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'textarea', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_getHelpKeys()),
		));
	}
}