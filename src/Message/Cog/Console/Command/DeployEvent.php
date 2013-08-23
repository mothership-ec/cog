<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;
use Message\Cog\Deploy;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DeployEvent
 *
 * Provides the deploy:event command.
 * Runs event listeners for deployment events.
 */
class DeployEvent extends Command
{
	protected function configure()
	{
		$this
			->setName('deploy:event')
			->setDescription('Runs event listeners for deployment events.')
			->addArgument('task', InputArgument::REQUIRED, 'The task that is being run by capistrano.')
			->addOption('before', null, InputOption::VALUE_NONE, 'Flag for fired before the task.')
			->addOption('after', null, InputOption::VALUE_NONE, 'Flag for fired after the task.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$task = $input->getArgument('task');

		if ($input->getOption('before')) {
			$this->get('event.dispatcher')->dispatch('cog.deploy.before.' . $task, new Deploy\Event\Event());
		} else {
			$this->get('event.dispatcher')->dispatch('cog.deploy.after.' . $task, new Deploy\Event\Event());
		}
	}
}