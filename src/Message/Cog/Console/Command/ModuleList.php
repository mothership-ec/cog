<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\TableFormatter;
use Message\Cog\Services;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ModuleList
 *
 * Provides the module:generate command.
 * Lists all loaded modules in the system.
 */
class ModuleList extends Command
{
	protected function configure()
	{
		$this
			->setName('module:list')
			->setDescription('Lists all loaded modules in the system.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$modules = (array) Services::get('config')->modules;

		$output->writeln('<info>Found ' . count($modules) . ' registered modules.</info>');

		$table = new TableFormatter(array('Name'));
		foreach($modules as $module) {
			$table->addRow(array($module));
		}
		$table->write($output);
	}
}
