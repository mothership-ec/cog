<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Status
 *
 * Provides the status command.
 */
class Status extends Command
{
	protected function configure()
	{
		$this
			->setName('status')
			->setDescription('Checks an installation to ensure it is setup correctly.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$event = new Event\Status($output);

		$this->get('event.dispatcher')->dispatch('console.status.check', $event);

		$event->summerise();
	}
}
