<?php

namespace Message\Cog\Controller;

use Message\Cog\Services;
use Message\Cog\HTTP\Request;

/**
 * Base controller class, providing helpers and common features
 * used by controllers.
 */
class Controller
{
	protected $_services;
	protected $_request;

	public function __construct()
	{
		$this->_services = Services::instance();
	}

	public function setRequest(Request $request)
	{
		$this->_request = $request;
	}

	/**
	 * Generate a URL from a route name.
	 *
	 * @param string 	$routeName 	Name of the route to use
	 * @param array 	$params	Parameters to use in the route
	 *
	 * @return string 				The generated URL
	 */
	public function generateUrl($routeName, $params = array())
	{
		return $this->_services['router']->generate($routeName, $params);
	}

	/**
	 * Returns a RedirectResponse for the given URL.
	 *
	 * @param string 	$url 	URL to redirect to
	 * @param int 		$status HTTP status code to use when redirecting
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302)
	{
		// TODO: redirect the user using a RedirectResponse
	}

	/**
	 * Triggers a sub-request for the given route name and returns a filtered
	 * Response object.
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

	public function render($reference, array $params = array())
	{
		if (!$this->_request instanceof Request) {
			throw new \LogicException('Request must be set on the controller to render a view.');
		}

		return $this->_services['response_builder']
			->setRequest($this->_request)
			->render($reference, $params);
	}
}