<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Bootstrap\EventsInterface;
use Message\Cog\HTTP\Event\Event as HTTPEvent;

/**
 * Cog event listener bootstrap.
 *
 * Registers Cog event listeners when the application is loaded.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Events implements EventsInterface, ContainerAwareInterface
{
	protected $_services;

	/**
	 * Inject the service container.
	 *
	 * @param ContainerInterface $serviceContainer The service container
	 */
	public function setContainer(ContainerInterface $serviceContainer)
	{
		$this->_services = $serviceContainer;
	}

	/**
	 * Register the event listeners & subscribers to the given event dispatcher.
	 *
	 * @param object $eventDispatcher The event dispatcher
	 */
	public function registerEvents($eventDispatcher)
	{
		// HTTP Component Events
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Request(
				$this->_services
			)
		);
		$eventDispatcher->addSubscriber(
			new \Message\Cog\HTTP\EventListener\Response($this->_services['http.cookies'])
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

		// Filesystem
		$eventDispatcher->addSubscriber(
			new \Message\Cog\Filesystem\EventListener(
				$this->_services
			)
		);

		// Routing
		$eventDispatcher->addSubscriber(new \Message\Cog\Routing\EventListener);

		// TODO: add a caching layer that just also subscribes to the request/response events
	}
}