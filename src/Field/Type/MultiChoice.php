<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\MultipleValueField;
use Symfony\Component\Form\FormBuilder;


/**
 * A field that provides a select menu of pre-defined options.
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class MultiChoice extends MultipleValueField
{
	public function __construct()
	{
		parent::__construct();
		$this->setFieldOptions(['multiple' => true]);
	}

	public function getFieldType()
	{
		return 'multichoice';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'choice', $this->getFieldOptions());
	}

	public function setFieldOptions(array $options)
	{
		if (array_key_exists('multiple', $options) && $options['multiple'] === false) {
			throw new \LogicException('For single choice fields, use the `choice` field type');
		}

		return parent::setFieldOptions($options);
	}

	public function getValueKeys()
	{
		if (!array_key_exists('choices', $this->_options)) {
			throw new \LogicException('`choices` key does not exist in field options');
		}

		return array_keys($this->_options['choices']);
	}

	public function setValues(array $values)
	{
		$keys = $this->getValueKeys();
		$newValues = [];

		foreach ($keys as $key) {
			$newValues[$key] = in_array($key, $values) ? $key : 0;
		}

		foreach ($newValues as $key => $value) {
			$this->setValue($key, $value);
		}
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