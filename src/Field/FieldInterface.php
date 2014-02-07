<?php

namespace Message\Cog\Field;

use Message\Cog\Validation\Validator;

/**
 * Interface defining a page field or a group of page fields.
 *
 * This is handy for hinting when you don't know if you will get a base field
 * or a group of fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface FieldInterface
{
	/**
	 * Constructor.
	 *
	 * @param string $name  Identifier name for this field (unique to the page type)
	 * @param string $label An optional human-readable label for this field
	 */
	public function __construct(Validator $validator);

	/**
	 * @return string
	 */
	public function getFieldType();

	/**
	 * Get the identifier name for this field.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Set the identifier name for this field
	 *
	 * @param $name
	 * @return FieldInterface
	 */
	public function setName($name);

	/**
	 * Get the human-readable label for this field.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Set the human readable label for this field.
	 *
	 * @param $label
	 * @return FieldInterface
	 */
	public function setLabel($label);

	/**
	 * Set the root translation key for this field.
	 *
	 * @param string $key The root translation key.
	 */
	public function setTranslationKey($key);

	/**
	 * Set options to pass to form
	 *
	 * @param array $options
	 */
	public function setOptions(array $options);
}