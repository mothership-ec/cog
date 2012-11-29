<?php

namespace Message\Cog\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
		);

		foreach($dirs as $dir) {
			$output->write('Creating directory `'.$dir.'` => ');
			if(is_dir($dir)) {
				$output->writeln('<comment>Already exists</comment>');
			} else {
				$output->writeln(mkdir($dir) ? '<info>Done</info>' : '<error>Failed</error>');
			}
			$output->write('Setting permissions on `'.$dir.'` => ');
			$output->writeln(chmod($dir, 0777) ? '<info>Done</info>' : '<error>Failed</error>');

		}
	}
}