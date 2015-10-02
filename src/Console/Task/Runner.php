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

		// Configure the printer handler
		$print = new OutputHandler\Printer;

		// Configure the mail handler
		$mail = new OutputHandler\Mail($container['mail.message'], $container['mail.dispatcher']);
		$mail->getMessage()
			->setTo($container['cfg']->app->defaultContactEmail)
			->setFrom($container['cfg']->app->defaultEmailFrom->email);

		// Configure the log handler
		$log = new OutputHandler\Log($container['log.console']);

		// Add handlers to command
		$command->addOutputHandler($print);
		$command->addOutputHandler($mail);
		$command->addOutputHandler($log);

		$app->add($command);
		$app->setAutoExit(false);

		$app->run(new StringInput($command->getName(). ' '. implode(' ', $arguments)));
	}
}