<?php

namespace Message\Cog\Field;

use Message\Cog\Validation\Validator;

/**
 * Basic content class.
 *
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 */
class Content implements ContentInterface
{
	protected $_fields = array();
	protected $_validator;

	/**
	 * @see set()
	 */
	public function __set($key, $value)
	{
		return $this->set($key, $value);
	}

	/**
	 * @see get()
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * @see exists()
	 */
	public function __isset($key)
	{
		return $this->exists($key);
	}

	/**
	 * Set a content part.
	 *
	 * @param string                             $key   Content part name
	 * @param FieldInterface|RepeatableContainer $value The content part
	 *
	 * @throws \InvalidArgumentException If the content part was not a valid instance
	 */
	public function set($key, $value)
	{
		if (!($value instanceof FieldInterface || $value instanceof RepeatableContainer)) {
			throw new \InvalidArgumentException(sprintf(
				'Page content must be a `FieldInterface` or a `RepeatableContainer`, `%s` given',
				get_class($value)
			));
		}

		$this->_fields[$key] = $value;
	}

	/**
	 * Get a content part by name.
	 *
	 * @param  string $key Content part name
	 *
	 * @return FieldInterface|RepeatableContainer $value The content part
	 */
	public function get($key)
	{
		return array_key_exists($key, $this->_fields) ? $this->_fields[$key] : null;
	}

	/**
	 * Check if a content part is set on this object.
	 *
	 * @param  string  $key Content part name
	 *
	 * @return boolean
	 */
	public function exists($key)
	{
		return isset($this->_fields[$key]);
	}

	/**
	 * Get the validator set on this object.
	 *
	 * @return Validator
	 */
	public function getValidator()
	{
		return $this->_validator;
	}

	/**
	 * Set the validator used for fields on this object.
	 *
	 * @param Validator $validator
	 */
	public function setValidator(Validator $validator)
	{
		$this->_validator = $validator;
	}

	/**
	 * Get the number of base fields & groups defined on this page content.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_fields);
	}

	/**
	 * Get the iterator to use for looping over this object.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_fields);
	}
}