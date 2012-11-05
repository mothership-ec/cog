<?php

namespace Message\Cog\Console;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

/**
 * Our wrapper around Symfony's Console component.
 *
 * The Console component runs tasks and helps make developing with Cog easier.
 */
class Factory
{
	public static function create()
	{
		$app = new Application;

		$app->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'local'));

		// Setup the default commands
		$app->add(new Command\ModuleGenerate);
		$app->add(new Command\ModuleList);
		$app->add(new Command\TaskRun);
		$app->add(new Command\TaskRunScheduled);
		$app->add(new Command\TaskGenerate);
		$app->add(new Command\TaskList);
		$app->add(new Command\ServicesList);
		$app->add(new Command\Setup);
		$app->add(new Command\TestUnitRun);

		return $app;
	}
}