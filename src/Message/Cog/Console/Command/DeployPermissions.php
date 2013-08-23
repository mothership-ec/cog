<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Deploy;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployPermissions
 *
 * Provides the deploy:permissions command.
 * Looks for directories to chmod on deploy.
 */
class DeployPermissions extends Command
{
	protected function configure()
	{
		$this
			->setName('deploy:permissions')
			->setDescription('Looks for directories to chmod on deploy.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$event = new Deploy\Event\Event();
		$this->get('event.dispatcher')->dispatch('cog.deploy.permissions', $event);

		foreach ($event->getLines() as $line) {
			$output->writeln($line);
		}
	}
}