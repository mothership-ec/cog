<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Services;
use Message\Cog\Console\TableFormatter;
use Message\Cog\Console\TaskRunner;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * ServicesList
 *
 * Provides the services:list command.
 * List all registered services.
 */
class ServicesList extends Command
{
	protected function configure()
	{
		$this
			->setName('services:list')
			->setDescription('List all registered services.')
			->addArgument('search_term', InputArgument::OPTIONAL, 'Display services matching [search_term]')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->getFormatter()->setStyle('bold', new OutputFormatterStyle(null, null, array('bold')));
		$term = $input->getArgument('search_term');

		$services = Services::instance()->getAll();
		ksort($services);

		foreach($services as $name => &$service) {
			$result = Services::get($name);

			if(is_object($result)) {
				$service = get_class($result);
			} else {
				$service = gettype($result) . '(' . var_export($result, true) . ')';
			}

			if(strlen($term) && strpos(strtolower($service.' '.$name), strtolower($term)) === false) {
				$service = false;
			}
		}

		$services = array_filter($services);
		$msg = 'Found %s registered services'.(strlen($term) ? ' matching `%s`' : '').'.';
		$output->writeln(sprintf('<info>'.$msg.'</info>', count($services), $term));
		$table = new TableFormatter(array('Name', 'Type'));
		foreach($services as $name => $service) {
			$table->addRow(array($name, $service));
		}

		$table->write($output);
	}
}