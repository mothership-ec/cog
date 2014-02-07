<?php

namespace Message\Cog\Field;

use Message\Cog\Validation;
use Message\Cog\Form\Handler;

/**
 * Base field object that should be inherited by both a normal field and a
 * "multiple value" field.
 *
 * Note that it's important that `setValue()` is not defined here, because the
 * method signatures are different for each subclass.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author James Moss <james@message.co.uk>
 */
abstract class BaseField implements FieldInterface, FieldContentInterface
{
	protected $_name;
	protected $_label;
	protected $_localisable = false;
	protected $_value;
	protected $_validator;
	protected $_group;
	protected $_translationKey;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(Validation\Validator $validator)
	{
		$this->_validator = $validator;
	}

	/**
	 * Print the class directly. This returns the field value.
	 *
	 * @return string The field value
	 */
	public function __toString()
	{
		return (string) $this->getValue();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName($name)
	{
		$this->_name	= $name;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setLabel($label)
	{
		$this->_label	= $label;

		return $this;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasContent()
	{
		return !empty($this->_value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType()
	{
		return gettype($this->_value);
	}

	/**
	 * Get the validator instance.
	 *
	 * @return Validator
	 */
	public function val()
	{
		return $this->_validator->addField(new Validation\Field($this->getName(), $this->getLabel()));
	}

	/**
	 * Checks if this field is localisable.
	 *
	 * @return boolean True if this field is localisable, false otherwise
	 */
	public function isLocalisable()
	{
		return $this->_localisable;
	}

	/**
	 * Toggle whether this field is localisable.
	 *
	 * @param boolean $localisable Whether the field should be localisable
	 */
	public function setLocalisable($localisable = true)
	{
		$this->_localisable = (bool) $localisable;

		return $this;
	}

	/**
	 * Set the root translation key for this field.
	 *
	 * @param string $key The root translation key
	 */
	public function setTranslationKey($key)
	{
		$this->_translationKey = $key . '.' . $this->getName();
	}

	/**
	 * Get the contextual help keys for this field, separated with a colon.
	 *
	 * The first key is the help key for this field type, formatted as:
	 * `ms.cms.field_types.[type].help`.
	 *
	 * The second is the help key for the specific content field, formatted as:
	 * `page.[pageType].[groupNameIfSet].[fieldName].help`
	 *
	 * @return string The contextual help keys separated with a colon.
	 */
	protected function _getHelpKeys()
	{
		$className = strtolower(get_class($this));
		$className = trim(strrchr($className, '\\'), '\\');

		return $this->_translationKey . '.help';
	}

	/**
	 * Add the form field for this field to a form handler instance.
	 *
	 * @param Handler $form The form handler instance
	 */
	abstract public function getFormField(Handler $form);
}