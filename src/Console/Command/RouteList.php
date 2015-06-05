<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

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
		ini_set('memory_limit', '2G');
		$routes = $this->get('routes.compiled');

		$output->writeln('<info>Found ' . count($routes) . ' registered routes.</info>');

		$table = $this->_getTable($output)->setHeaders(array('Name', 'Path', 'Method', 'Format', 'Controller'));

		foreach($routes as $name => $route) {
			$defaults = $route->getDefaults();

			$row = array(
				$name,
				$route->getPath(),
				$route->getMethods() ? implode('|', $route->getMethods()) : 'ANY',
				$defaults['_format'] ? $defaults['_format'] : 'ANY',
				is_scalar($defaults['_controller']) ? $defaults['_controller'] : gettype($defaults['_controller']),
			);

			$table->addRow($row);
		}

		$table->render($output);
	}
}
