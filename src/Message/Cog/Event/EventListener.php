<?php

namespace Message\Cog\Event;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * Event listener for the Filesystem component.
 *
 * @author James Moss <james@message.co.uk>
 */
abstract class EventListener implements ContainerAwareInterface
{
	protected $_services;
	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}
}