<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to set up a new Cog installation.
 */
class Setup extends Command
{
	protected function configure()
	{
		$this
			->setName('setup')
			->setDescription('Sets up a Cog installation.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dirs = array(
			'tmp/',
			'logs/',
			'translations/',
			'config/',
		);

		// Get the root of the installation
		$root = $this->get('app.loader')->getBaseDir();

		$output->writeln('<bold>Installation root `'.$root.'`</bold>');

		foreach($dirs as $dir) {
			$output->write('  Creating directory `'.$dir.'` => ');
			
			if(is_dir($root.$dir)) {
				$output->writeln('<comment>Already exists</comment>');
			} else {
				$output->writeln(mkdir($root.$dir) ? '<info>Done</info>' : '<error>Failed</error>');
			}

			$output->write('  Setting permissions on `'.$dir.'` => ');
			$output->writeln(chmod($root.$dir, 0777) ? '<info>Done</info>' : '<error>Failed</error>');
		}
	}
}