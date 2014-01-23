<?php

namespace Message\Cog\Console\Task;

use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Input\StringInput;

/**
* Runner.
*
* A factory for creating an instance of a Task\Application, adding a task
* to it and running it.
*/
class Runner
{
	/**
	 * Constructor
	 *
	 * @param Task               $command   The task to run
	 * @param ContainerInterface $container A instance of the service container
	 * @param array              $arguments An array of arguments to pass to the task.
	 */
	public function __construct(Task $command, ContainerInterface $container, array $arguments)
	{
		$app = new Application('Cog task runner');
		$app->setContainer($container);

		$command->addOutputHandler(new OutputHandler\Printer);
		$command->addOutputHandler(new OutputHandler\Mail($container['mail.message'], $container['mail.dispatcher']));
		$command->addOutputHandler(new OutputHandler\Log($container['log.errors']));

		// Output to the console by default
		$command->output('print')->enable();

		$app->add($command);
		$app->setAutoExit(false);

		$app->run(new StringInput($command->getName(). ' '. implode(' ', $arguments)));
	}
}