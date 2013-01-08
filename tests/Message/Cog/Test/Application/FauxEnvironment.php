<?php

namespace Message\Cog\Test\Application;

/**
 * A simple Environment implementation to aid unit testing.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxEnvironment
{
	protected $_context;

	/**
	 * Set the context.
	 *
	 * @param string $context The context to set
	 */
	public function set($context)
	{
		$this->_context = $context;
	}

	/**
	 * Get the context.
	 *
	 * @return string The context set on this class
	 */
	public function context()
	{
		return $this->_context;
	}
}