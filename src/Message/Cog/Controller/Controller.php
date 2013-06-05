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
	 * @see \Message\Cog\Routing\UrlGenerator::generate()
	 *
	 * @param  string $routeName Name of the route to use
	 * @param  array  $params    Parameters to use in the route
	 * @param  bool   $absolute  True to return an absolute URL
	 *
	 * @return string            The generated URL
	 */
	public function generateUrl($routeName, $params = array(), $absolute = false)
	{
		return $this->_services['routing.url_generator']->generate($routeName, $params, $absolute);
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
	 * Triggers a sub-request for the given controller reference and returns a
	 * filtered Response instance.
	 *
	 * @param  string $reference  The reference for the controller
	 * @param  array  $attributes Request attributes
	 * @param  array  $query      Optional query (GET) parameters
	 * @param  bool   $catch      True to catch exceptions thrown in the sub-request
	 *
	 * @return Response           The filtered Response instance
	 */
	public function forward($reference, array $attributes = array(), array $query = array(), $catch = true)
	{
		// Copy the route format from the current request
		$attributes['_format']     = $this->_services['request']->attributes->get('_format');
		// Set the Symfony controller reference
		$attributes['_controller'] = $this->_services['reference_parser']->parse($reference)->getSymfonyLogicalControllerName();

		$kernel  = $this->_services['http.kernel'];
		$request = $this->_services['request']->duplicate($query, null, $attributes);

		// Execute the sub-request
		return $kernel->handle($request, $kernel::MASTER_REQUEST, $catch);
	}

	/**
	 * Render a view and return the rendered contents as a HTTP Response
	 * instance.
	 *
	 * @see ResponseBuilder::render
	 *
	 * @param  string $reference The reference for the view
	 * @param  array  $params    Optional parameters to pass to the view
	 *
	 * @return Response          The rendered view as a HTTP Response instance
	 *
	 * @throws LogicException    If the Request is not set on this class
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