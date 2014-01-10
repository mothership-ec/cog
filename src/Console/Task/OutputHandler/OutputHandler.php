<?php

namespace Message\Cog\Console\Task\OutputHandler;

use Message\Cog\Console\Task\Task;

abstract class OutputHandler
{
	protected $_output = false;
	protected $_task;

	abstract public function getName();
	abstract public function process(array $args);

	public function enable()
	{
		$this->_output = true;
	}

	public function disable()
	{
		$this->_output = false;
	}

	public function setTask(Task $task)
	{
		$this->_task = $task;
	}
}