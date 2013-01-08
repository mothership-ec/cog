<?php

namespace Message\Cog\Console;

/**
 *
 */
class TaskCollection
{
	protected $_tasks = array();

	public function add(Task $task, $description)
	{
		$description = trim($description);
		if(empty($description)) {
			throw new \InvalidArgumentException('No description provided for '.$task->getName());
		}

		$this->_tasks[$task->getName()] = array(
			\Message\Cog\Service\Container::get('fns.utility')->traceCallingModuleName(),
			$description,
			$task,
		);
	}

	public function get($name)
	{
		return isset($this->_tasks[$name]) ? $this->_tasks[$name] : false;
	}

	public function all()
	{
		return $this->_tasks;
	}
}