<?php

namespace Message\Cog\Field;

/**
 * Interface for any type of page element that may contain content. This can be either a field or
 * an iterable collection of fields. This interface exists to prevent problems where logic may require
 * either.
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
interface FieldContentInterface
{
	/**
	 * Check to see if a field, or collection, has content assigned.
	 *
	 * @return bool         Return true if content exists
	 */
	public function hasContent();

	/**
	 * Return string to determine content type
	 *
	 * @return string
	 */
	public function getType();
}