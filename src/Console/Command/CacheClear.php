<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Deploy;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * DeployEvent
 *
 * Provides the deploy:event command.
 * Runs event listeners for deployment events.
 */
class CacheClear extends Command
{
	protected function configure()
	{
		$this
			->setName('cache:clear')
			->setDescription('Clears the cache folder.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('Clearing trasure chest caches...');
		$this->get('cache')->clear();

		$output->writeln('Clearing twig caches...');
		$this->get('templating.twig.environment')->clearCacheFiles();

		$output->writeln('Done');
	}
}