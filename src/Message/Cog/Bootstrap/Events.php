<?php

namespace Message\Cog\Bootstrap;

use Message\Cog\Services as ServiceContainer;
use Message\Cog\Module\Bootstrap\EventsInterface;
use Message\Cog\HTTP\Event\Event as HTTPEvent;

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

		// TODO: add a caching layer that just also subscribes to the request/response events
		// TODO: bags of stuff could be moved to these events to clean up code, for example, the Profiler

		// NOTE: When the new CMS is built, we just need to add a new listener
		// or two here that routes requests to the CMS (providing routes fail)! Magic.
	}
}