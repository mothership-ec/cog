<?php

namespace Message\Cog\Console\Task;

use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Input\StringInput;

/**
* TaskRunner.
*
* A factory for creating an instance of a TaskApplication, adding a task 
* to it and running it.
*/
class Runner
{
	/**
	 * Constructor
	 *
	 * @param Task               $command   The task to run
	 * @param ContainerInterface $container A instance of the service container
	 */
	public function __construct(Task $command, ContainerInterface $container)
	{
		$app = new Application('Cog task runner');
		$app->setContainer($container);
		$app->add($command);
		$app->setAutoExit(false);
		$app->run(new StringInput($command->getName()));
	}
}