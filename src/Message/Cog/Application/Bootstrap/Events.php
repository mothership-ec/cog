<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog\Service\Container as ServiceContainer;
use Message\Cog\Bootstrap\EventsInterface;
use Message\Cog\HTTP\Event\Event as HTTPEvent;

/**
 * Cog event listener bootstrap.
 *
 * Registers Cog event listeners when the application is loaded.
 *
 * @todo When this can access Services in a better way, update this
 */
class Events implements EventsInterface
{
	protected $_services;

	public function __construct(ServiceContainer $serviceContainer)
	{
		$this->_services = $serviceContainer;
	}

	public function registerEvents($eventDispatcher)
	{
		// HTTP Component Events
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Request(
				$this->_services,
				$this->_services['router']
			)
		);
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Response
		);
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Exception
		);

		// Profiler
		$eventDispatcher->addSubscriber(
			new \Message\Cog\Debug\EventListener(
				$this->_services['profiler'],
				$this->_services['environment']
			)
		);

		// TODO: add a caching layer that just also subscribes to the request/response events
		// TODO: bags of stuff could be moved to these events to clean up code, for example, the Profiler

		// NOTE: When the new CMS is built, we just need to add a new listener
		// or two here that routes requests to the CMS (providing routes fail)! Magic.
	}
}