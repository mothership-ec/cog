<?php

namespace Message\Cog\Bootstrap;

/**
 * Bootstrap interface for registering commands to the command collection.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface CommandsInterface extends BootstrapInterface
{
	/**
	 * Register commands to the given command collection.
	 *
	 * The command collection is not type hinted because this would mean we would
	 * have to type hint it in every class that uses this interface which is
	 * unmanageable.
	 *
	 * We can assume that `$tasks` is an instance of
	 * `\Message\Cog\Console\CommandCollection`.
	 *
	 * @param object $commands The command collection
	 */
	public function registerCommands($commands);
}