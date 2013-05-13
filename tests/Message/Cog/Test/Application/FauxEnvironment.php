<?php

namespace Message\Cog\Test\Application;

use Message\Cog\Application\EnvironmentInterface;

/**
 * A simple Environment implementation to aid unit testing.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxEnvironment implements EnvironmentInterface
{
	protected $_name;
	protected $_installation;
	protected $_context;

	public function set($name)
	{
		$this->_name = $name;
	}

	public function setInstallation($installation)
	{
		$this->_installation = $installation;
	}

	/**
	 * Set the context.
	 *
	 * @param string $context The context to set
	 */
	public function setContext($context)
	{
		$this->_context = $context;
	}

	public function get()
	{
		return $this->_name;
	}

	public function installation()
	{
		return $this->_installation;
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