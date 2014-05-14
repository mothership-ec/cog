<?php

namespace Message\Cog\Field;

/**
 * Represents a page content field that has multiple, separate values.
 *
 * This is useful for attaching metadata to a field or for special field types
 * that have a few peices of data that need indexing and storing separately (for
 * example, a product selector might have a product and colour ID).
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class MultipleValueField extends BaseField
{
	protected $_value = array();

	/**
	 * @see setValue()
	 */
	public function __set($name, $value)
	{
		$this->setValue($name, $value);
	}

	/**
	 * Print the class directly. This returns all of the field values
	 * concatenated with a colon character.
	 *
	 * {@inheritdoc}
	 *
	 * @return string The field values as a string
	 */
	public function __toString()
	{
		return implode(':', $this->_value);
	}

	/**
	 * Get a field property.
	 *
	 * @param  string $name Property name
	 *
	 * @return mixed        The value of the field property
	 *
	 * @throws \OutOfBoundsException If the field property does not exist
	 */
	public function __get($name)
	{
		if (isset($this->_value[$name])) {
			return $this->_value[$name];
		}

		throw new \OutOfBoundsException(sprintf('Field value does not exist: `%s`', $name));
	}

	/**
	 * Check if a value is set on this field.
	 *
	 * @param  string  $name Value name
	 *
	 * @return boolean       True if the value is set
	 */
	public function __isset($name)
	{
		return isset($this->_value[$name]);
	}

	/**
	 * Set all values for this field.
	 *
	 * @param array $values Array of values
	 */
	public function setValues(array $values)
	{
		foreach ($values as $name => $value) {
			$this->setValue($name, $value);
		}
	}

	/**
	 * Add a value to this field.
	 *
	 * If an array is passed as the first argument, the array is assumed to be
	 * an array of values, where the key is the value key. In this case, the
	 * array is proxied to `setValues()`.
	 *
	 * @see   setValues
	 *
	 * @param array|string $key   The field key, or an array of keys & values
	 * @param mixed|null   $value The field value
	 *
	 * @throws \InvalidArgumentException If the value key is falsey
	 * @throws \InvalidArgumentException If the value key is not valid (does not
	 *                                   exist in self::getValueKeys())
	 */
	public function setValue($key, $value = null)
	{
		if (is_array($key)) {
			return $this->setValues($key);
		}

		if (!$key) {
			throw new \InvalidArgumentException('Field value must have a key');
		}

		if (!in_array($key, $this->getValueKeys())) {
			throw new \InvalidArgumentException(sprintf(
				'Value key `%s` invalid. Allowed names: `%s`',
				$key,
				implode('`, `', $this->getValueKeys())
			));
		}

		$this->_value[$key] = $value;
	}

	/**
	 * Get the keys allowed for values in this field.
	 *
	 * @return array An array of allowed keys
	 */
	abstract public function getValueKeys();
}