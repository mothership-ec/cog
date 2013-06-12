<?php

namespace Message\Cog\ImageResize\Bootstrap;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Bootstrap\EventsInterface;

/**
 * Cog event listener bootstrap.
 *
 * Registers ImageResize event listeners when the application is loaded.
 *
 * @author James Moss <james@message.co.uk>
 */
class Events implements EventsInterface, ContainerAwareInterface
{
	protected $_services;

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $serviceContainer)
	{
		$this->_services = $serviceContainer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function registerEvents($eventDispatcher)
	{
		$eventDispatcher->addSubscriber(new \Message\Cog\ImageResize\EventListener);
	}
}