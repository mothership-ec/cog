<?php

namespace Message\Cog\Console\Task\OutputHandler;


/**
 * OutputHandler that prints to the command line when a task is run.
 *
 * We have to call this Printer instead of Print as it's a reserved word.
 */
class Printer extends OutputHandler
{
	/**
	 * {inheritDoc}
	 */
	public function getName()
	{
		return 'print';
	}

	/**
	 * {inheritDoc}
	 */
	public function process(array $args)
	{
		if($this->_output) {
			$this->_task->getRawOutput()->writeln($args[0]);
		}
	}
}