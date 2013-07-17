<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TaskList
 *
 * Provides the task:list command.
 * List all registered tasks.
 */
class TaskList extends Command
{
	protected function configure()
	{
		$this
			->setName('task:list')
			->setDescription('List all registered tasks.')
			->addArgument('module_name', InputArgument::OPTIONAL, 'Only return tasks registered by a specific module')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$module_name = $input->getArgument('module_name');

		$tasks = array();
		foreach($this->get('task.collection')->all() as $task) {
			if($module_name && $module_name !== $task[0]) {
				continue;
			}

			$tasks[] = $task;
		}

		$output->writeln('<info>Found ' . count($tasks) . ' registered tasks.</info>');

		$table = $this->getHelperSet()->get('table')
			->setHeaders(array('Name', 'Description', 'Scheduled', 'Next run date'));

		ksort($tasks);
		foreach($tasks as $task) {
			$scheduled = '';
			$next      = '';
			if($cron = $task[2]->getCronExpression()) {
				$scheduled = $cron->getExpression();
				$next      = $cron->getNextRunDate()->format('d/m/y H:i T');
				if(count($envs = $task[2]->getCronEnvironments()) && !in_array($this->get('env'), $envs)) {
					$next = 'Only runs on '.implode('|', $envs);
				}
			}
			$table->addRow(array($task[2]->getName(), $task[1], $scheduled, $next));
		}
		$table->render($output);
	}
}
