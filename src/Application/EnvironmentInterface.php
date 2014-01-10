<?php

namespace Message\Cog\Application;

/**
 * Interface for the Environment class.
 *
 * Responsible for determining and storing environment information such as:
 *
 *  * The context
 *  * The environment name
 *  * The installation name
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface EnvironmentInterface
{
	/**
	 * Gets the current environment name.
	 *
	 * @return string The current environment name
	 */
	public function get();

	/**
	 * Sets the current environment, overriding the detected environment.
	 *
	 * @param string $name A valid environment name to change to
	 */
	public function set($name);

	/**
	 * Gets the name of the current context.
	 *
	 * @return string The current context name
	 */
	public function context();

	/**
	 * Gets the name of the current installation.
	 *
	 * @return string The current installation name
	 */
	public function installation();
}