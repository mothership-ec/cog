<?php

namespace Message\Cog\Controller;

use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;
use Message\Cog\HTTP\RedirectResponse;
use Message\Cog\HTTP\RequestAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

use LogicException;

/**
 * Base controller class, providing helpers and common features used by
 * controllers.
 *
 * Controller classes don't have to extend this class, but they will often find
 * it useful to.
 *
 * @todo Add a helper method for adding feedback once the Feedback component is
 *       built.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Controller implements ContainerAwareInterface, RequestAwareInterface
{
	protected $_services;
	protected $_request;

	/**
	 * Sets the service container.
	 *
	 * @param ContainerInterface $container The service container instance
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	/**
	 * Set the current HTTP request on this controller.
	 *
	 * This is used by `ResponseBuilder` to help determine the response format
	 * type.
	 *
	 * @param Request $request The request to set
	 */
	public function setRequest(Request $request)
	{
		$this->_request = $request;
	}

	/**
	 * Generate a URL from a route name.
	 *
	 * @param  string $routeName Name of the route to use
	 * @param  array  $params    Parameters to use in the route
	 *
	 * @return string            The generated URL
	 */
	public function generateUrl($routeName, $params = array())
	{
		return $this->_services['router']->generate($routeName, $params);
	}

	/**
	 * Returns a RedirectResponse instance for redirection to the given URL.
	 *
	 * @param  string $url 	  URL to redirect to
	 * @param  int    $status HTTP status code to use when redirecting
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302)
	{
		return new RedirectResponse($url, $status);
	}

	/**
	 * Triggers a sub-request for the given route name and returns a filtered
	 * Response instance.
	 *
	 * @param  string $routeName  Name of the route to execute
	 * @param  array  $attributes Request attributes
	 * @param  array  $query      Optional query (GET) parameters
	 *
	 * @return Response           The filtered Response instance
	 */
	public function forward($routeName, array $attributes = array(), array $query = array())
	{
		return $this->_services['http.dispatcher']->forward($routeName, $attributes, $query);
	}

	/**
	 * Render a view and return the rendered contents as a HTTP Response
	 * instance.
	 *
	 * @param  string $reference The reference for the view
	 * @param  array  $params    Optional parameters to pass to the view
	 *
	 * @return Response          The rendered view as a HTTP Response instance
	 */
	public function render($reference, array $params = array())
	{
		if (!$this->_request instanceof Request) {
			throw new LogicException('Request must be set on the controller to render a view.');
		}

		return $this->_services['response_builder']
			->setRequest($this->_request)
			->render($reference, $params);
	}
}