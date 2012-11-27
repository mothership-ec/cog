<?php

namespace Message\Cog\Bootstrap;

use Message\Cog\Service\Container as ServiceContainer;
use Message\Cog\Module\Bootstrap\EventsInterface;
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
	public function registerEvents($eventDispatcher)
	{
		// HTTP Component Events
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Request(
				ServiceContainer::instance(),
				ServiceContainer::get('router')
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
			new \Message\Cog\Profiler\EventListener(
				ServiceContainer::get('profiler'),
				ServiceContainer::get('environment')
			)
		);

		// TODO: add a caching layer that just also subscribes to the request/response events
		// TODO: bags of stuff could be moved to these events to clean up code, for example, the Profiler

		// NOTE: When the new CMS is built, we just need to add a new listener
		// or two here that routes requests to the CMS (providing routes fail)! Magic.
	}
}