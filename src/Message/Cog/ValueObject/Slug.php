<?php

namespace Message\Cog\ValueObject;

/**
 * Represents a resource slug.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Slug implements \IteratorAggregate, \Countable
{
	protected $_segments;

	/**
	 * Constructor.
	 *
	 * @param array|string $segments An array of the slug segments, or the full
	 *                               slug as a string
	 */
	public function __construct($segments)
	{
		if (!is_array($segments)) {
			$segments = explode('/', $segments);
		}

		$this->_segments = array_values(array_filter($segments)); // reset numeric indices
	}

	/**
	 * Get the full slug as a string, with a forward slash separating each
	 * segment, prepended with a forward slash.
	 *
	 * @return string The full slug as a string
	 */
	public function getFull()
	{
		return '/' . implode('/', $this->_segments);
	}

	/**
	 * Return the array of segments that make the url
	 *
	 * @return array $this->_segments
	 */
	public function getSegments()
	{
		return $this->_segments;
	}

	/**
	 * Return the last segment of the url
	 *
	 * @return string
	 */
	public function getLastSegment()
	{
		return end($this->_segments);
	}

	/**
	 * Get an external iterator to use for this class.
	 *
	 * @return \ArrayIterator Iterator for the slug segments
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_segments);
	}

	/**
	 * Get the number of slug segments
	 *
	 * @return int The number of slug segments
	 */
	public function count()
	{
		return count($this->_segments);
	}

	/**
	 * @see getFull()
	 */
	public function __toString()
	{
		return $this->getFull();
	}
}