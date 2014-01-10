<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Filesystem\File;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the migrate:run command. Runs new migrations for all registered
 * modules.
 */
class MigrateRun extends Command
{
	protected function configure()
	{
		$this
			->setName('migrate:run')
			->setDescription('Runs all migrations for cogules that have not yet been run.')
			// ->addOption('path', null, InputArgument::OPTIONAL, 'Path to search for migrations.', File::COG_PREFIX . '://migrations/')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Running module migrations...</info>');

		// Get the loaded modules
		$modules = $this->get('module.loader')->getModules();

		foreach ($modules as $module) {

			// Get the reference
			$reference = '@' . str_replace('\\', ':', $module);

			try {
				$this->get('migration')->run($reference);
			}
			catch (\Message\Cog\DB\Exception $e) {
				throw new \Exception("Migration error: Have you run `migrate:install`?", null, $e);
			}

			// Output this migration's notes
			foreach ($this->get('migration')->getNotes() as $note) {
				$output->writeln($note);
			}

			// Clear the notes
			$this->get('migration')->clearNotes();
		}
	}
}