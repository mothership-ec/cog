<?php

namespace Message\Cog\Event;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Wrapper around Symfony's `EventDispatcher` class so we're not exposing
 * any Symfony code to the rest of Cog.
 *
 * This extends our own `DispatcherInterface` which can be used for type
 * hinting. Also injects the service container if the subscriber implements
 * ContainerAwareInterface
 */
class Dispatcher extends \Symfony\Component\EventDispatcher\EventDispatcher implements DispatcherInterface
{
	protected $_services;

	public function __construct(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	public function addSubscriber(EventSubscriberInterface $subscriber)
	{
		if($subscriber instanceof ContainerAwareInterface) {
			$subscriber->setContainer($this->_services);
		}

		return parent::addSubscriber($subscriber);
	}
}