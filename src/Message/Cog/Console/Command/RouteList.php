<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Service\Container as ServiceContainer;

use Message\Cog\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RouteList
 *
 * Provides the module:generate command.
 * Lists all loaded modules in the system.
 */
class RouteList extends Command
{
	protected function configure()
	{
		$this
			->setName('route:list')
			->setDescription('Lists all loaded routes in the system.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$routes = ServiceContainer::get('routes.compiled');

		$output->writeln('<info>Found ' . count($routes) . ' registered routes.</info>');

		$table = $this->getHelperSet()->get('table')
			->setHeaders(array('Name', 'Path', 'Method', 'Format', 'Controller'));

		foreach($routes as $name => $route) {

			$defaults = $route->getDefaults();
			$table->addRow(array(
				$name,
				$route->getPath(),
				$route->getMethods() ? implode('|', $route->getMethods()) : 'ANY',
				$defaults['_format'] ? implode('|', $defaults['_format']) : 'ANY',
				$defaults['_controller']
			));
		}

		$table->render($output);
	}
}
