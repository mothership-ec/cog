<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Routing\RouterInterface;
use Message\Cog\HTTP\Event\Event;
use Message\Cog\HTTP\Dispatcher;
use Message\Cog\HTTP\StatusException;

use Symfony\Component\Routing\Exception\ExceptionInterface as RouterException;

/**
 * Event listener for core functionality for dealing with requests.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Request implements SubscriberInterface
{
	protected $_services;
	protected $_router;

	static public function getSubscribedEvents()
	{
		return array(Event::REQUEST => array(
			array('addRequestToServices'),
			array('routeRequest'),
			array('validateRequestScope'),
			array('validateRequestedFormats'),
		));
	}

	/**
	 * Constructor.
	 *
	 * @param Services        $services The service container
	 * @param RouterInterface $router   The router
	 */
	public function __construct(ContainerInterface $services, RouterInterface $router)
	{
		$this->_services = $services;
		$this->_router   = $router;
	}

	/**
	 * Adds the current request to the service container with the key `request`.
	 *
	 * @param Event $event The Event::REQUEST event instance
	 */
	public function addRequestToServices(Event $event)
	{
		$this->_services['request'] = $this->_services->share(function() use ($event) {
			return $event->getRequest();
		});
	}

	/**
	 * Routes a request and sets the appropriate request attributes
	 * (such as _controller) that are used by the ControllerResolver.
	 *
	 * @param Event $event The Event::REQUEST event instance
	 */
	public function routeRequest(Event $event)
	{
		try {
			$uri = $event->getRequest()->getRequestUri();

			// If this is an internal request, overwrite the URI with the evaluated route
			if ($event->getRequest()->isInternal()) {
				// Get the route name
				$attributes = clone $event->getRequest()->attributes;
				$routeName  = $attributes->get('_route');
				// Remove the _route attribute so it doesn't confuse the route matcher later
				$attributes->remove('_route');
				// Generate the URI for the named route
				$uri = $this->_router->generate($routeName, $attributes->all());
			}

			// Ask the router for a match for this request
			$match = $this->_router->match($uri);

			// Set all route attributes as attributes on the request
			foreach ($match as $attr => $val) {
				$event->getRequest()->attributes->set($attr, $val);
			}
		}
		// Turn any uncaught RouterExceptions into HTTP StatusExceptions
		catch (RouterException $e) {
			// Get exception class name without namespaces
			$exceptionClass = explode('\\', get_class($e));
			switch (end($exceptionClass)) {
				case 'ResourceNotFoundException':
				case 'RouteNotFoundException':
					throw new StatusException(
						'Request could not be routed to a response',
						StatusException::NOT_FOUND,
						$e
					);
					break;
				case 'MethodNotAllowedException':
					throw new StatusException(
						'Not allowed',
						StatusException::NOT_ALLOWED,
						$e
					);
					break;
			}
		}
	}

	/**
	 * Validates that the request's route can be accessed in the given scope.
	 * This stops internal-only routes from being accessed externally.
	 *
	 * If this is the case, we throw a 404 instead of the correct HTTP status
	 * code, a 403. This is because we don't want the visitor to know there is
	 * an internal resource here. The exception message should still be accurate
	 * though as it won't be displayed to the visitor.
	 *
	 * @param Event $event     The Event::REQUEST event instance
	 * @throws StatusException If the route is internal-only and the request is external
	 */
	public function validateRequestScope(Event $event)
	{
		if ($event->getRequest()->isExternal() && $event->getRequest()->attributes->get('_access')) {
			if (Dispatcher::REQUEST_INTERNAL === $event->getRequest()->attributes->get('_access')) {
				throw new StatusException(
					'This resource cannot be accessed externally.',
					StatusException::NOT_FOUND
				);
			}
		}
	}

	/**
	 * Validates that the requested formats are allowed by the route, and
	 * tells the request which requested formats can be used. This is then used
	 * when building the response.
	 *
	 * @param Event $event     The Event::REQUEST event instance
	 * @throws StatusException If none of the requested content type(s) are acceptable
	 */
	public function validateRequestedFormats(Event $event)
	{
		// If this request's route has specific format(s) set
		if ('ANY' !== $event->getRequest()->attributes->get('_format')) {
			// Determine the content type to return based on what's allowed and what's requested
			$allowedFormats         = explode('|', $event->getRequest()->attributes->get('_format'));
			$requestedContentTypes  = $event->getRequest()->getAcceptableContentTypes();
			$allowedContentTypes    = array();

			// Loop through requested content types
			foreach ($requestedContentTypes as $mimeType) {
				// Get format from the mime type
				$formatType = $event->getRequest()->getFormat($mimeType);
				// If this content type is available, add the mimetype to the accepted list
				if (in_array($formatType, $allowedFormats)) {
					$allowedContentTypes[] = $mimeType;
				}
			}

			// If none of the requested content types were acceptable, throw exception
			if (empty($allowedContentTypes)) {
				throw new StatusException(
					sprintf('Unnaceptable content type(s) requested: `%s`', implode(', ', $requestedContentTypes)),
					StatusException::NOT_ACCEPTABLE
				);
			}
			// Otherwise, set the list of acceptable content types on the request for later use
			else {
				$event->getRequest()->attributes->set('_allowedContentTypes', $allowedContentTypes);
			}
		}
	}
}