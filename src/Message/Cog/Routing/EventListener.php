<?php

namespace Message\Cog\Routing;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Event\EventListener as BaseListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Component\HttpKernel\KernelEvents;

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
			),
			KernelEvents::REQUEST => array(
				array('checkCsrf'),
			),
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

	/**
	 * Checks if the matched route has had CSRF protection enabled. If it 
	 * does then it checks that the hash is present and correct.
	 *
	 * @param  GetResponseEvent $event The incoming response
	 *
	 * @return void
	 */
	public function checkCsrf(GetResponseEvent $event)
	{
		$attributes = $event->getRequest()->attributes;

		if($csrfKey = $attributes->get(Route::CSRF_ATTRIBUTE_NAME)) {
			$routeName = $attributes->get('_route');
			$route     = $this->_services['routes.compiled']->get($routeName);

			// Ensure that the session is started before we try to get it's ID.
			$this->_services['http.session']->start();

			$calculatedHash = $route->getCsrfToken(
				$routeName,
				$attributes->get('_route_params'),
				$this->_services['http.session']->getId(),
				$this->_services['routing.csrf_secret']
			);

			if($attributes->get($csrfKey) !== $calculatedHash) {
				throw new AccessDeniedHttpException('CSRF is invalid.');
			}
		}
	}
}