<?php

namespace Message\Cog\HTTP;

use Message\Cog\Service\Container as ServiceContainer;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Controller\ControllerResolverInterface;
use Message\Cog\Event\DispatcherInterface;

/**
 * Request dispatcher.
 *
 * This class is responsible for handling requests, sending them to the router
 * and building the appropriate responses.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Dispatcher
{
	const REQUEST_EXTERNAL = 'external';
	const REQUEST_INTERNAL = 'internal';

	protected $_eventDispatcher;
	protected $_controllerResolver;
	protected $_masterRequest;

	/**
	 * Constructor.
	 *
	 * @param DispatcherInterface         $router   The event dispatcher to use for event firing
	 * @param ControllerResolverInterface $resolver The controller resolver to used to help execute the controller
	 * @param Request|null                $request  The master request
	 */
	public function __construct(DispatcherInterface $dispatcher, ControllerResolverInterface $resolver, Request $request = null)
	{
		$this->_eventDispatcher    = $dispatcher;
		$this->_controllerResolver = $resolver;
		$this->_masterRequest      = $request;
	}

	/**
	 * Handles a Request and returns the appropriate Response.
	 *
	 * @param  Request $request The request instance to handle
	 * @param  string  $type    The type of request ('internal' or 'external')
	 * @param  boolean $catch   Whether exceptions should be caught within this method or not
	 *
	 * @return Response         The appropriate Response instance
	 */
	public function handle(Request $request, $type = self::REQUEST_EXTERNAL, $catch = true)
	{
		if ($type == self::REQUEST_INTERNAL) {
			$request->setInternal();
		}

		try {
			// Fire request event; these events will set the request attributes used by ControllerResolver
			$this->_eventDispatcher->dispatch(
				Event\Event::REQUEST,
				new Event\Event($this, $request)
			);

			// Ask the controller resolver to find the relevant controller and arguments for this request
			$controller = $this->_controllerResolver->getController($request);
			$arguments  = $this->_controllerResolver->getArguments($request, $controller);

			// If the controller implements ContainerAwareInterface, set the container
			if ($controller[0] instanceof ContainerAwareInterface) {
				$controller[0]->setContainer(ServiceContainer::instance());
			}

			// If the controller implements RequestAwareInterface, set the request
			if ($controller[0] instanceof RequestAwareInterface) {
				$controller[0]->setRequest($request);
			}

			// Call the controller
			$response = call_user_func_array($controller, $arguments);

			// If the controller doesn't directly return a Response object, fire requests to build one
			if (!$response instanceof Response) {
				// Initialise response setting event
				$event = new Event\BuildResponseFromResultEvent($this, $request, $response);
				// Fire response event
				$this->_eventDispatcher->dispatch(
					Event\Event::RESPONSE_BUILD,
					$event
				);
				// Get the response set by the event listener
				if (!$response = $event->getResponse()) {
					// If no response is set, throw exception
					throw new \LogicException('Response was not set for request.');
				}
			}
		}
		catch (\Exception $e) {
			// If the handler was asked not to catch exceptions, re-throw the exception
			if (false === $catch) {
				throw $e;
			}

			$response = $this->_handleException($e, $request);
		}

		return $this->_filterResponse($response, $request);
	}

	/**
	 * Triggers a sub-request for the given route name and returns a filtered
	 * Response object.
	 *
	 * @param  string $routeName  Name of the route to execute
	 * @param  array  $attributes Request attributes
	 * @param  array  $query      Optional query (GET) parameters
	 * @param  bool   $catch      Whether exceptions should be caught within this method or not
	 *
	 * @return Response           The filtered Response instance
	 */
	public function forward($routeName, array $attributes = array(), array $query = array(), $catch = true)
	{
		$attributes['_route'] = $routeName;
		$subRequest = $this->_masterRequest->duplicate($query, null, $attributes);

		return $this->handle($subRequest, self::REQUEST_INTERNAL, $catch);
	}

	/**
	 * Handles an exception by firing an event asking listeners to turn it into
	 * an appropriate error Response object.
	 *
	 * @param  \Exception $exception The exception that has been caught
	 * @param  Request    $request   The Request instance
	 *
	 * @return Response              The generated Response instance
	 */
	protected function _handleException(\Exception $exception, $request)
	{
		$event = new Event\BuildResponseFromExceptionEvent($this, $request, $exception);
		$this->_eventDispatcher->dispatch(
			Event\Event::EXCEPTION,
			$event
		);

		// Event listeners may have replaced the exception, so get it again
		$exception = $event->getException();

		// Throw the exception again if we don't have a Response
		if (!$event->hasResponse()) {
			throw $exception;
		}

		$response = $event->getResponse();

		// Ensure the Response returned is a proper error Response (or a redirect)
		if (!$response->isError() && !$response->isRedirect()) {
			// If the exception was a StatusException, carry over the status code
			if ($exception instanceof StatusException) {
				$response->setStatusCode($exception->getCode());
			}
			// Otherwise, just set the status code to 500 (Internal Server Error)
			else {
				$response->setStatusCode(StatusException::SERVER_ERROR);
			}
		}

		return $response;
	}

	/**
	 * Fires Event\Event::RESPONSE event so listeners can make changes to
	 * the response if required.
	 *
	 * @param  Response $response The unfiltered Response
	 * @param  Request  $request  The associated Request
	 *
	 * @return Response           The filtered Response
	 */
	protected function _filterResponse(Response $response, Request $request)
	{
		$event = new Event\FilterResponseEvent($this, $request, $response);
		$this->_eventDispatcher->dispatch(
			Event\Event::RESPONSE,
			$event
		);

		return $event->getResponse();
	}
}