<?php

namespace Message\Cog\Console;

use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Input\InputOption;

/**
 * Our wrapper around Symfony's Console component.
 *
 * The Console component runs tasks and helps make developing with Cog easier.
 */
class Factory
{
	const ENV_OPT_NAME = 'env';

	public static function create(ContainerInterface $container)
	{
		$app = new Application;
		$app->setContainer($container);

		$app->getDefinition()->addOption(
			new InputOption('--' . self::ENV_OPT_NAME, '', InputOption::VALUE_OPTIONAL, 'The Environment name.')
		);

		// Setup the default commands
		$app->add(new Command\ModuleGenerate);
		$app->add(new Command\ModuleList);
		$app->add(new Command\RouteList);
		$app->add(new Command\RouteCollectionTree);
		$app->add(new Command\ServiceList);
		$app->add(new Command\Setup);
		$app->add(new Command\TaskGenerate);
		$app->add(new Command\TaskList);
		$app->add(new Command\TaskRun);
		$app->add(new Command\TaskRunScheduled);

		return $app;
	}
}