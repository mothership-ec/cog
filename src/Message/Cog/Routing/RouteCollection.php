<?php

namespace Message\Cog\Routing;

use Message\Cog\ReferenceParserInterface;
use Symfony\Component\Routing\RouteCollection as SFRouteCollection;

/**
 * Wrapper around Symfony's `RouteCollection` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class RouteCollection 
{
	protected $_collection;

	/**
	 * Constructor.
	 *
	 * @api
	 */
	public function __construct(ReferenceParserInterface $referenceParser)
	{
		$this->_referenceParser = $referenceParser;
		$this->_collection      = new SFRouteCollection;
	}

	/**
	 * Add a route to the router.
	 *
	 * @param string $name       A valid route name
	 * @param string $url        A route URL
	 * @param string $controller The controller/method to execute upon a successful match
	 * 
	 * @return Route The newly added route
	 */
	public function add($name, $url, $controller)
	{
		$reference = $this->_referenceParser->parse($controller);
		$defaults  = array(
			'_controller' => $reference->getSymfonyLogicalControllerName()
		);
		$route     = new Route($url, $defaults);

		$this->_collection->add($name, $route);

		return $route;
	}

	public function getRouteCollection()
	{
		return $this->_collection;
	}
}