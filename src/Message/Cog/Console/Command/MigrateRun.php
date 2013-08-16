<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrateRun
 *
 * Provides the migrate:run command. Runs new migrations at the path.
 */
class MigrateRun extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:run')
			->setAliases('migrate')
			->setDescription('Runs all migrations at the given path that have not yet been run.')
			->addArgument('path', InputArgument::OPTIONAL, 'Path to search for migrations.', 'migrations/')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Running migrations...</info>');

		$path = $input->getArgument('path');

		$this->get('db.migrate')->run($path);

		foreach ($this->get('db.migrate')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}