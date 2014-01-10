<?php

namespace Message\Cog\HTTP;

/**
 * A container for all cookies available to the system.
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class CookieCollection implements \IteratorAggregate, \Countable
{
	protected $_cookies = array();

	/**
	 * Constructor.
	 *
	 * @param array|null $cookies An array of cookies to add
	 */
	public function __construct(array $cookies = null)
	{
		if (is_array($cookies)) {
			foreach ($cookies as $name => $cookie) {
				$this->add($cookie);
			}
		}
	}

	/**
	 * Add a cookie to this collection.
	 *
	 * @param Cookie $cookie The cookie to add
	 *
	 * @return Cookie         Returns $this for chainability
	 */
	public function add(Cookie $cookie)
	{
		$this->_cookies[] = $cookie;

		return $this;
	}

	/**
	 * Get the number of cookies registered on this collection.
	 *
	 * @return int The number of cookies registered
	 */
	public function count()
	{
		return count($this->_cookies);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_cookies`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_cookies);
	}
}