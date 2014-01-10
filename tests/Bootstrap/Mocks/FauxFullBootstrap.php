<?php

namespace Message\Cog\Test\Bootstrap\Mocks;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Cog\Bootstrap\RoutesInterface;
use Message\Cog\Bootstrap\EventsInterface;
use Message\Cog\Bootstrap\TasksInterface;
/**
 * Faux bootstrap class implementing all bootstrap type interfaces.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxFullBootstrap implements ServicesInterface, RoutesInterface,
	EventsInterface, TasksInterface
{
	public function registerServices($serviceContainer)
	{
	}

	public function registerRoutes($router)
	{
	}

	public function registerEvents($eventDispatcher)
	{
	}

	public function registerTasks($taskCollection)
	{
	}
}