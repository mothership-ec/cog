<?php

namespace Message\Cog\Templating;

use Message\Cog\Service\ContainerInterface;

/**
 * Container for global variables available to templates.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class GlobalVariables
{
	protected $_services;
	protected $_variables;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $services The service container
	 */
	public function __construct(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Get a defined variable.
	 *
	 * @param  string $name Global variable name
	 *
	 * @return mixed        Global variable value
	 *
	 * @throws \InvalidArgumentException If the global variable has not been set
	 */
	public function __get($name)
	{
		if (!isset($this->_variables[$name])) {
			throw new \InvalidArgumentException(sprintf('Global variable `%s` not set', $name));
		}

		return $this->_variables[$name];
	}

	/**
	 * Check if a given global variable has been set.
	 *
	 * @param  string $name Global variable name
	 *
	 * @return boolean      True if the variable is set, false otherwise
	 */
	public function __isset($name)
	{
		return isset($this->_variables[$name]);
	}

	/**
	 * Add a new global variable to this container.
	 *
	 * @param string   $name   Name for the global variable
	 * @param callable $define Callable to define the variable value, the first
	 *                         and only parameter passed to this is the service
	 *                         container.
	 *
	 * @throws \InvalidArgumentException If the name is already in use
	 * @throws \InvalidArgumentException If the definition is not callable
	 */
	public function set($name, $define)
	{
		if (isset($this->_variables[$name])) {
			throw new \InvalidArgumentException(sprintf('Global variable name `%s` already in use', $name));
		}

		if (!is_callable($define)) {
			throw new \InvalidArgumentException(sprintf('Definition for global variable `%s` is not callable', $name));
		}

		$this->_variables[$name] = $define($this->_services);
	}
}