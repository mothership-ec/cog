<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Symfony\Component\Form\FormBuilder;


/**
 * A field that provides a select menu of pre-defined options.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Choice extends Field
{
	public function getFieldType()
	{
		return 'choice';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'choice', $this->getFieldOptions());
	}

	/**
	 * Set the options available on this select menu.
	 *
	 * @param array $choices Array of options
	 *
	 * @return Choice    Returns $this for chainability
	 */
	public function setOptions(array $choices)
	{
		$this->_options['choices'] = $choices;

		return $this;
	}
}