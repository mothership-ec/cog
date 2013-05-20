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

		$this->_segments = $segments;
	}

	/**
	 * Get the full slug as a string, with a forward slash separating each
	 * segment.
	 *
	 * @return string The full slug as a string
	 */
	public function getFull()
	{
		return implode('/', $this->_segments);
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