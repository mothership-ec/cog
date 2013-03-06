<?php

namespace Message\Cog\Test\Bootstrap\Mocks;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Cog\Bootstrap\RoutesInterface;
use Message\Cog\Bootstrap\EventsInterface;
use Message\Cog\Bootstrap\TasksInterface;

/**
 * Faux Bootstrap class that implements all bootstrap type interfaces and logs
 * when each register method is called.
 *
 * This is useful for inspecting which register methods have been called on the
 * bootstrap, and in what order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class MethodCallOrderTesterBootstrap implements ServicesInterface,
	RoutesInterface, EventsInterface, TasksInterface
{
	protected $_calls = array();

	public function registerServices($serviceContainer)
	{
		$this->_calls[] = 'registerServices';
	}

	public function registerRoutes($router)
	{
		$this->_calls[] = 'registerRoutes';
	}

	public function registerEvents($eventDispatcher)
	{
		$this->_calls[] = 'registerEvents';
	}

	public function registerTasks($taskCollection)
	{
		$this->_calls[] = 'registerTasks';
	}

	/**
	 * Get the array of register method calls.
	 *
	 * @return array Array of register method calls
	 */
	public function getCalls()
	{
		return $this->_calls;
	}

	/**
	 * Clear the log of register method calls.
	 */
	public function clearCalls()
	{
		$this->_calls = array();
	}
}