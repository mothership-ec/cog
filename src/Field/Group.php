<?php

namespace Message\Cog\Field;

use Message\Cog\Validation\Validator;
use Message\Cog\Validation\Field as ValidatorField;


/**
 * Represents a group of page content fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Group implements FieldInterface, FieldContentInterface
{
	protected $_name;
	protected $_label;
	protected $_validator;
	protected $_translationKey;

	protected $_repeatable = false;
	protected $_repeatableMin;
	protected $_repeatableMax;

	protected $_fields = array();
	protected $_idFieldName;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(Validator $validator)
	{
		$this->_validator = $validator;
//		$this->_name      = $name;
//		$this->_label     = $label ?: $name;
	}

	/**
	 * Get a field in this group.
	 *
	 * @param  string $name Field name
	 *
	 * @return mixed        The field
	 *
	 * @throws \OutOfBoundsException If the field does not exist
	 */
	public function __get($name)
	{
		if (isset($this->_fields[$name])) {
			return $this->_fields[$name];
		}

		throw new \OutOfBoundsException(sprintf('Group field does not exist: `%s`', $name));
	}

	public function getFieldType()
	{
		return 'group';
	}

	/**
	 * Check if a field exists in this group
	 *
	 * @param  string  $name Field name
	 *
	 * @return boolean       True if the field exists on this group
	 */
	public function __isset($name)
	{
		return isset($this->_fields[$name]);
	}

	/**
	 * Post-cloning method call. This performs "deep cloning" of the object, by
	 * cloning each field.
	 *
	 * This method is important because without it, when the group is cloned,
	 * the fields within the group will still point to the original instances
	 * rather than being cloned themselves.
	 */
	public function __clone()
	{
		foreach ($this->_fields as $name => $field) {
			$this->_fields[$name] = clone $field;
		}
	}

	public function val()
	{
		return $this->getValidator();
	}

	public function getValidator()
	{
		return $this->_validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_name;
	}

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

	public function setLabel($label)
	{
		$this->_label	= $label;

		return $this;
	}

	/**
	 * Check if this group is a repeatable group.
	 *
	 * @return boolean
	 */
	public function isRepeatable()
	{
		return $this->_repeatable;
	}

	/**
	 * Get the field set as the "identifier field".
	 *
	 * @return Field|false The field instance, or false if no identifier field set
	 */
	public function getIdentifierField()
	{
		return $this->_idFieldName ? $this->_fields[$this->_idFieldName] : false;
	}

	/**
	 * Add a field to this group.
	 *
	 * If the field has one of the following names and an "identifier field"
	 * has not yet been set on this group, the field will be used as the
	 * identifier:
	 *
	 *  - id
	 *  - identifier
	 *  - title
	 *  - heading
	 *
	 * @param Field  $field The field to add
	 *
	 * @return Group        Returns $this for chainability
	 */
	public function add(FieldInterface $field)
	{
		$field->setTranslationKey($this->_translationKey . '.fields');
		$this->_fields[$field->getName()] = $field;

		// If no identifier field is set yet and this field is a good candidate, set it
		if (!$this->getIdentifierField() && in_array($field->getName(), array(
			'id',
			'identifier',
			'title',
			'heading',
			'name',
		))) {
			$this->setIdentifierField($field->getName());
		}

		$this->_validator->addField(new ValidatorField($field->getName(), $field->getLabel()));

		return $this;
	}

	/**
	 * Set repeatable information for this group.
	 *
	 * As well as being repeatable, a group can have a minimum and maximum
	 * number of repeats.
	 *
	 * @param boolean   $repeatable True to make this group repeatable, false
	 *                              otherwise
	 * @param int|null  $min        Minimum number of times this group can be
	 *                              repeated
	 * @param int|null  $max        Maximum number of times this group can be
	 *                              repeated
	 *
	 * @return Group                Returns $this for chainability
	 */
	public function setRepeatable($repeatable = true, $min = null, $max = null)
	{
		$this->_repeatable = (bool) $repeatable;

		if ($min) {
			$this->_repeatableMin = (int) $min;
		}

		if ($max) {
			$this->_repeatableMax = (int) $max;
		}

		return $this;
	}

	/**
	 * Set the field to use as a "identifier field".
	 *
	 * @param string $fieldName Name of the field to use
	 *
	 * @throws \InvalidArgumentException If a field with this name doesn't exist
	 *                                   in this group
	 */
	public function setIdentifierField($fieldName)
	{
		if (!isset($this->_fields[$fieldName])) {
			throw new \InvalidArgumentException(sprintf(
				'Field `%s` does not exist on this group.',
				$fieldName
			));
		}

		$this->_idFieldName	= $fieldName;

		return $this;
	}

	/**
	 * Get all fields within this group
	 *
	 * @return array Array of fields in this group.
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * {@inheritDoc}
	 * @throws \InvalidArgumentException        Throws exception if field does not extend
	 *                                          FieldContentInterface
	 */
	public function hasContent()
	{
		$hasContent = false;
		foreach ($this->getFields() as $field) {
			if ($field instanceof FieldContentInterface) {
				$hasContent = ($field->hasContent()) ? true : $hasContent;
			}
			else {
				throw new \InvalidArgumentException('Field must implement FieldContentInterface');
			}
		}

		return $hasContent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType()
	{
		return __CLASS__;
	}

	/**
	 * Set the root translation key to use for this group.
	 *
	 * @param string $key The root translation key to use
	 */
	public function setTranslationKey($key)
	{
		$this->_translationKey = $key . '.' . $this->getName();
	}
}