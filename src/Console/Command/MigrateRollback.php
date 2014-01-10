<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrateRollback
 *
 * Provides the migrate:rollback command. Rolls back the last migration batch.
 */
class MigrateRollback extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:rollback')
			->setDescription('Rolls back the last migration batch.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Rolling back last migration batch...</info>');

		$this->get('migration')->rollback();

		foreach ($this->get('migration')->getNotes() as $note) {
			$output->writeln($note);
		}
	}
}