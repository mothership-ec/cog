<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ServicesList
 *
 * Provides the services:list command.
 * List all registered services.
 *
 * @deprecated This class is deprecated because it is too unstable to use in practice. It loads every single
 *             service in the service container, but if any have issues the whole script will break. This makes
 *             it better as a tool for debugging but not very useful as a way to list services.
 */
class ServiceList extends Command
{
	protected function configure()
	{
		$this
			->setName('service:list')
			->setDescription('List all registered services.')
			->addArgument('search_term', InputArgument::OPTIONAL, 'Display services matching [search_term]')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$term = $input->getArgument('search_term');

		$services = $this->_services->keys();
		$result = array();
		natsort($services);

		foreach($services as $name) {
			try {
				$serviceResult = $this->get($name);

				if(is_object($serviceResult)) {
					$service = get_class($serviceResult);
				} else {
					$export = var_export($serviceResult, true);
					if(strlen($export) > 80) {
						$export = substr($export, 0, 80).'...';
					}
					$service = gettype($serviceResult) . '(' . str_replace("\n", '\n', $export) . ')';
				}

				if(strlen($term) && strpos(strtolower($service.' '.$name), strtolower($term)) === false) {
					continue;
				}
			} catch(\Exception $e) {
				$service = 'ERROR';
			}

			$result[$name] = $service;
		}

		$msg = 'Found %s registered services'.(strlen($term) ? ' matching `%s`' : '').'.';
		$output->writeln(sprintf('<info>'.$msg.'</info>', count($result), $term));

		$table = $this->_getTable($output)
			->setHeaders(array('Name', 'Type'));

		foreach($result as $name => $service) {
			$table->addRow(array($name, $service));
		}

		$table->render($output);
	}
}