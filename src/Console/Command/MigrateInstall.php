<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrateInstall
 *
 * Provides the migrate:run command. Runs new migrations at the path.
 */
class MigrateInstall extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:install')
			->setDescription('Installs the migration database table.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Installing migrations...</info>');

		$this->get('migration.mysql.loader')->install();

		foreach ($this->get('migrator')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}