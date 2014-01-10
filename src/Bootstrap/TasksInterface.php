<?php

namespace Message\Cog\Bootstrap;

/**
 * Bootstrap interface for registering tasks to a task collection.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface TasksInterface extends BootstrapInterface
{
	/**
	 * Register tasks to the given task collection.
	 *
	 * The task collection is not type hinted because this would mean we would
	 * have to type hint it in every class that uses this interface which is
	 * unmanageable.
	 *
	 * We can assume that `$tasks` is an instance of
	 * `\Message\Cog\Console\TaskCollection`.
	 *
	 * @param object $tasks The task collection
	 */
	public function registerTasks($tasks);
}