<?php

namespace Message\Cog\ValueObject;

/**
 * Represents a resource slug.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Slug implements \IteratorAggregate, \Countable
{
	// Characters not always picked up by iconv()
	private $_substitutions = [
		'ð' => 'd',
		'Đ' => 'D',
		'ø' => 'o',
		'Ø' => 'O',
	];

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
	 * @return array segments that make up the url
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
	 * Sanitize each segment of the slug.
	 *
	 * Custom substitutions can be passed in as the first argument, with the
	 * key as the character(s) to substitute and the value as the substitution
	 * value. This defaults to one substitution of '&' => 'and'.
	 *
	 * After this, the slug is transliterated (if `iconv` is available); made
	 * lowercase and any non-alphanumeric characters are replaced with hyphens.
	 *
	 * If this results in sequential hyphens, they are replaced with a single
	 * hyphen to stop slugs like 'my-blog--post'. Finally, any hyphens at the
	 * start or end of the segment are trimmed.
	 *
	 * @param  array  $substitutions Array of substitutions
	 *
	 * @return Slug                  Returns $this for chainability
	 */
	public function sanitize(array $substitutions = array('&' => 'and'))
	{
		// Only replace accented characters declared by $this->_substitutions if iconv() exists as otherwise
		// there would be an inconsistency in the final result
		if (function_exists('iconv')) {
			$substitutions = array_merge($this->_substitutions, $substitutions);
		}

		foreach ($this->_segments as $i => $segment) {
			// Perform substitutions
			foreach ($substitutions as $find => $replace) {
				$segment = str_replace($find, $replace, $segment);
			}
			// Transliterate
			if (function_exists('iconv')) {
				$segment = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $segment);
				// Remove any weird characters added by the transliteration
				$segment = str_replace(array('"', '\'', '`', '^'), '', $segment);
			}
			// Lowercase
			$segment = strtolower($segment);
			// Replace any non-alphanumeric characters with hyphens
			$segment = preg_replace('/[^a-z0-9]/i', '-', $segment);
			// Set any double hyphens to just a single hyphen
			$segment = preg_replace('/-+/i', '-', $segment);
			// Remove any hyphens at the start or end
			$segment = trim($segment, '-');

			$this->_segments[$i] = $segment;
		}

		return $this;
	}

	/**
	 * @see getFull()
	 */
	public function __toString()
	{
		return $this->getFull();
	}
}