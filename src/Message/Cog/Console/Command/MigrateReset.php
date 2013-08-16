<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrateReset
 *
 * Provides the migrate:run command. Runs new migrations at the path.
 */
class MigrateReset extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:reset')
			->setDescription('Resets the database to the state before any migrations were run.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Resetting all migrations...</info>');

		$this->get('db.migrate')->reset();

		foreach ($this->get('db.migrate')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}