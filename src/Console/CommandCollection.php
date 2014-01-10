<?php

namespace Message\Cog\Console;

/**
 * A container for all console commands available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class CommandCollection implements \IteratorAggregate, \Countable
{
	protected $_commands = array();

	/**
	 * Constructor.
	 *
	 * @param array $groups An array of commands to add
	 */
	public function __construct(array $commands = array())
	{
		foreach ($commands as $command) {
			$this->add($command);
		}
	}

	/**
	 * Add a command to this collection.
	 *
	 * @param  Command $command  The command to add
	 *
	 * @return CommandCollection Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If a command with the same name has
	 *                                   already been set on this collection
	 */
	public function add(Command $command)
	{
		if (isset($this->_commands[$command->getName()])) {
			throw new \InvalidArgumentException(sprintf('Command `%s` is already defined', $command->getName()));
		}

		$this->_commands[$command->getName()] = $command;

		return $this;
	}

	/**
	 * Get a command set on this collection by name.
	 *
	 * @param  string $name The command name
	 *
	 * @return Command      The command instance
	 *
	 * @throws \InvalidArgumentException If the command has not been set
	 */
	public function get($name)
	{
		if (!isset($this->_commands[$name])) {
			throw new \InvalidArgumentException(sprintf('Command `%s` not set on collection', $name));
		}

		return $this->_commands[$name];
	}

	/**
	 * Get the number of commands registered on this collection.
	 *
	 * @return int The number of commands registered
	 */
	public function count()
	{
		return count($this->_commands);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_commands`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_commands);
	}
}