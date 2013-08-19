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
			->addOption('path', null, InputArgument::OPTIONAL, 'Path to search for migrations.', './migrations/')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $input->getOption('path');

		$output->writeln('<info>Running migrations in ' . $path . '...</info>');

		try {
			$this->get('migration')->run($path);
		}
		catch (\Message\Cog\DB\Exception $e) {
			throw new \Exception("Migration error: Have you run `migrate:install`?", null, $e);
		}

		foreach ($this->get('migration')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}