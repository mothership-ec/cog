<?php

namespace Message\Cog\Console\Command;

use Message\Cog\Services;
use Message\Cog\Console\TaskRunner;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TaskRun
 *
 * Provides the task:run command.
 * Runs a single task.
 */
class TaskRun extends Command
{
    protected function configure()
    {
        $this
            ->setName('task:run')
            ->setDescription('Run a task.')
            ->addArgument('task_name', InputArgument::REQUIRED, 'The full name of the task.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('task_name');

        if(!$task = Services::get('task.collection')->get($name)) {
            $output->writeln('<error>Task `'.$name.'` does not exist.</error>');
            return;
        }

        $command = $task[2];
        $runner = new TaskRunner($command);
    }
}
