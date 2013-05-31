<?php

namespace Message\Cog\Routing;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * Event listener for the Routing component.
 *
 * @author James Moss <james@message.co.uk>
 */
class EventListener implements SubscriberInterface, ContainerAwareInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array('modules.load.success' => array(
			array('mountRoutes'),
		));
	}

	/**
	 * setContainer
	 *
	 * @inherit
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Mount the default routes on the router, ready to be matched.
	 */
	public function mountRoutes()
	{
		$this->_services['router']->setRouteCollection($this->_services['routes']->compileRoutes()->getRouteCollection());
	}
}