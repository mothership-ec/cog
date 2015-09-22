<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the migrate:run command. Runs new migrations for all registered
 * modules.
 */
class MigrateUninstall extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:uninstall')
			->setDescription('Rolls back all migrations on for a module. Use colon delimited syntax (i.e. \'Message:Mothership:Commerce\' to uninstall commerce module).')
			->addArgument('module_name', InputArgument::REQUIRED, 'Uninstall databases for a specific module.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Uninstalling databases for ' . $input->getArgument('module_name') . '...</info>');

		$this->get('migrator')->rollbackModule($input->getArgument('module_name'));

		foreach ($this->get('migrator')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}