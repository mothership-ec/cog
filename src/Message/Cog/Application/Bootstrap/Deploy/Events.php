<?php

namespace Message\Cog\Application\Bootstrap\Deploy;

use Message\Cog\Bootstrap\EventsInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

class Events implements EventsInterface, ContainerAwareInterface
{
	protected $_services;

	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new EventListener);
	}
}