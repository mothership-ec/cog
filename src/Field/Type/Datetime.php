<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

/**
 * A field for a single date & time.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Datetime extends Field
{
	public function __toString()
	{
		return $this->_value;
	}

	public function getFieldType()
	{
		return 'datetime';
	}

	public function getValue()
	{
		if ($this->_value instanceof \DateTime) {
			return $this->_value;
		}

		return new \DateTime(date('c', $this->_value));
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'datetime', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_getHelpKeys()),
		));
	}
}