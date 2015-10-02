<?php

namespace Message\Cog\Field;

interface ContentInterface extends \IteratorAggregate, \Countable
{
	/**
	 * Set a content part.
	 *
	 * @param string                             $var   Content part name
	 * @param FieldInterface|RepeatableContainer $value The content part
	 *
	 * @throws \InvalidArgumentException If the content part was not a valid instance
	 */
	public function set($key, $value);

	/**
	 * Get a content part by name.
	 *
	 * @param  string $var Content part name
	 *
	 * @return FieldInterface|RepeatableContainer $value The content part
	 */
	public function get($key);

	/**
	 * Check if a content part is set on this object.
	 *
	 * @param  string  $var Content part name
	 *
	 * @return boolean
	 */
	public function exists();

	/**
	 * Get the number of base fields & groups defined on this page content.
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Get the iterator to use for looping over this object.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator();
}