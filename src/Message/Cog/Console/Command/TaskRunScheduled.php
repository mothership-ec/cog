<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Service\Container as ServiceContainer;

use Message\Cog\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;


/**
 * TaskList
 *
 * Provides the task:run_scheduled command.
 * Runs all scheduled tasks.
 *
 * This command needs to be fired every minute by a cronjob. It checks all
 * registered tasks and determines which ones need to be run. These are then
 * asynchronously launched as seperate processes which run independantly.
 *
 * The entry in the crontab file needs to look something like this:
 * 		* * * * * /path/to/site/bin/cog --env=live task:run_scheduled > /dev/null 2>&1
 */
class TaskRunScheduled extends Command
{
	protected function configure()
	{
		$this
			->setName('task:run_scheduled')
			->setDescription('Runs all scheduled tasks.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$path = $_SERVER['argv'][0];
		$env  = ' --env='.ServiceContainer::get('env');
		foreach(ServiceContainer::get('task.collection')->all() as $task) {
			if($task[2]->isDue(time(), ServiceContainer::get('env'))) {
				$output->writeln('Running ' . $task[2]->getName());
				try {
					$process = new Process($path . $env . ' task:run ' . $task[2]->getName());
					$process->start();
				} catch (\Exception $e) {
					$output->writeln('Error: ' . $e->getMessage());
				}
			}
		}
	}
}
