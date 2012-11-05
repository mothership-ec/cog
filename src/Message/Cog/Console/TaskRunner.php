<?php

namespace Message\Cog\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;

/**
* TaskRunner
*/
class TaskRunner
{
	public function __construct(Task $command)
	{
		$app = new TaskApplication('Cog task runner');
		$app->add($command);
		$app->setAutoExit(false);
		$app->run(new StringInput($command->getName()));
	}
}