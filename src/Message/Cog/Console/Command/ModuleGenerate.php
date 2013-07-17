<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ModuleGenerate
 *
 * Provides the module:generate command.
 * TODO: implement this class.
 */
class ModuleGenerate extends Command
{
	protected function configure()
	{
		$this
			->setName('module:generate')
			->setDescription('Generates an empty module from a useful default.')
			->addArgument('module_name', InputArgument::REQUIRED, 'The full name of the module, including namespace.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<error>This command needs implementing.</error>');
		return;

		$name = $input->getArgument('module_name');

		$path = '/some/example/path';

		// check if it already exists
		$output->writeln('<error>Module `'.$name.'` already exists in `'.$path.'`.</error>');
	}
}
