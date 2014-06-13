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
			->addOption('task', null, InputOption::VALUE_REQUIRED, 'The task that is being run by the deploy process.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$task = $input->getOption('task');

		$event = new Deploy\Event\Event();

		$event->setInput($input);
		$event->setOutput($output);
		$event->setCommandCollection($this->_services['console.commands']);

		mail('laurence@message.co.uk', 'deploy event', 'deploy.' . $task);
		$this->get('event.dispatcher')->dispatch('deploy.' . $task, $event);
	}
}