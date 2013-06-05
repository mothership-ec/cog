<?php

namespace Message\Cog\Routing;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Event\EventListener as BaseListener;

/**
 * Event listener for the Routing component.
 *
 * @author James Moss <james@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'modules.load.success' => array(
				array('mountRoutes'),
			)
		);
	}

	/**
	 * Compile the routes from the CollectionManager and add them to the
	 * router, ready to be matched against a request.
	 */
	public function mountRoutes()
	{
		$compiledRoutes = $this->_services['routes']->compileRoutes()->getRouteCollection();
		$this->_services['router']->setRouteCollection($compiledRoutes);
	}
}