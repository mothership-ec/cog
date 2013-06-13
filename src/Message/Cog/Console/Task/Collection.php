<?php

namespace Message\Cog\Console\Task;

use Message\Cog\Service\ContainerInterface;

/**
 * TaskCollection
 *
 * Maintains a list of tasks.
 */
class Collection
{
	protected $_tasks = array();

	public function __construct(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Add a task to the collection
	 *
	 * @param Task   $task        The task to be added
	 * @param string $description A short description explaining what the task does.
	 */
	public function add(Task $task, $description)
	{
		// Descriptions must be provided!
		$description = trim($description);
		if(empty($description)) {
			throw new \InvalidArgumentException('No description provided for '.$task->getName());
		}

		// Add the task to the internal array
		$this->_tasks[$task->getName()] = array(
			$this->_services['fns.utility']->traceCallingModuleName(),
			$description,
			$task,
		);
	}

	/**
	 * Get a task from the collection
	 *
	 * @param  string $name The name of the task to return
	 *
	 * @return Task|boolean Returns the task if it exists, false otherwise.
	 */
	public function get($name)
	{
		return isset($this->_tasks[$name]) ? $this->_tasks[$name] : false;
	}

	/**
	 * Get all tasks in this collection
	 *
	 * @return array An array of Task objects
	 */
	public function all()
	{
		return $this->_tasks;
	}
}