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
	 *
	 * Once the routes are compiled, the routing event subscriber for HTTP
	 * requests is registered to the event dispatcher.
	 */
	public function mountRoutes()
	{
		$this->_services['routes.compiled'] = $this->_services->share(function($c) {
			return $c['routes']->compileRoutes()->getRouteCollection();
		});

		// Now the routes are compiled, we can add the routing event listener for HTTP requests
		$this->_services['event.dispatcher']->addSubscriber(
			new \Symfony\Component\HttpKernel\EventListener\RouterListener($this->_services['routing.matcher'])
		);
	}
}