<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

/**
 * A field that provides a select menu of pre-defined options.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Choice extends Field
{
	protected $_options;

	public function getFieldType()
	{
		return 'choice';
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'choice', $this->getLabel(), array(
			'attr'    => array('data-help-key' => $this->_getHelpKeys()),
			'choices' => $this->_options,
		));
	}

	/**
	 * Set the options available on this select menu.
	 *
	 * @param array $options Array of options
	 *
	 * @return SelectMenu    Returns $this for chainability
	 */
	public function setOptions(array $options)
	{
		$this->_options = $options;

		return $this;
	}
}