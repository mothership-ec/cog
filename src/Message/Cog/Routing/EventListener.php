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
		return array(
			'modules.load.success' => array(
				array('mountRoutes'),
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Compile the routes from the CollectionManager and add them to the
	 * router, ready to be matched against a request.
	 */
	public function mountRoutes()
	{
		$compiledRoutes = $this->_services['routes']->compileRoutes()->getRouteCollection();

		// TODO: move the below somewhere more suitable - we shouldn't register listeners here!
		$this->_services['routing.matcher'] = function($c) use ($compiledRoutes) {
			return new UrlMatcher($compiledRoutes, $c['http.request.context']);
		};

		$this->_services['routing.url_generator'] = $this->_services->share(function($c) use ($compiledRoutes) {
			return new UrlGenerator($compiledRoutes, $c['http.request.context']);
		});

		$this->_services['event.dispatcher']->addSubscriber(
			new \Symfony\Component\HttpKernel\EventListener\RouterListener($this->_services['routing.matcher'])
		);
	}
}