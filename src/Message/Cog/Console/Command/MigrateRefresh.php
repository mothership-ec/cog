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
class MigrateRefresh extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:refresh')
			->setDescription('Refreshes the database by resetting then running all migrations.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Refreshing migrations...</info>');

		$this->get('db.migrate')->refresh();

		foreach ($this->get('db.migrate')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}