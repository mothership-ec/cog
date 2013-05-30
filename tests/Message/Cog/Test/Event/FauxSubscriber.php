<?php

namespace Message\Cog\Test\Event;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\Event;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * Event listener for the Routing component.
 *
 * @author James Moss <james@message.co.uk>
 */
class FauxSubscriber implements SubscriberInterface, ContainerAwareInterface
{
	protected $_container;
	
	static public function getSubscribedEvents()
	{
		return array();
	}

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	public function getContainer()
	{
		return $this->_container;
	}
}