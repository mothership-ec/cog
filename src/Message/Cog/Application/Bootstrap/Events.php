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
 */
class Events implements EventsInterface, ContainerAwareInterface
{
	protected $_services;

	public function setContainer(ContainerInterface $serviceContainer)
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
	}
}