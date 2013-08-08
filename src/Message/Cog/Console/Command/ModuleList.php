<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
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
		$modules = $this->get('module.loader')->getModules();

		$output->writeln('<info>Found ' . count($modules) . ' registered modules.</info>');

		$table = $this->getHelperSet()->get('table')
			->setHeaders(array('Name'));

		foreach($modules as $module) {
			$table->addRow(array($module));
		}

		$table->render($output);
	}
}
