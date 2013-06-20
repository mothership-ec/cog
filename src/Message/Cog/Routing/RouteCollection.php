<?php

namespace Message\Cog\Routing;

use Message\Cog\Module\ReferenceParserInterface;
use Symfony\Component\Routing\RouteCollection as SFRouteCollection;

/**
 * Wrapper around Symfony's `RouteCollection` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class RouteCollection
{
	protected $_name;
	protected $_collection;
	protected $_prefix = '';
	protected $_parent;

	/**
	 * Constructor.
	 *
	 * @param ReferenceParserInterface $referenceParser	The reference parser to
	 *                                                  parse controller refs
	 * @param string                   $name	        The name for this collection
	 */
	public function __construct(ReferenceParserInterface $referenceParser, $name = '')
	{
		$this->_referenceParser = $referenceParser;
		$this->_name            = $name;
		$this->_collection      = new SFRouteCollection;
	}

	/**
	 * Add a route to the underlying RouteCollection.
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
			'_controller'      => $reference->getSymfonyLogicalControllerName(),
		);
		$route     = new Route($url, $defaults);

		$this->_collection->add($name, $route);

		return $route;
	}

	/**
	 * Get Symfony's RouteCollection
	 *
	 * @return RouteCollection The underlying RouteCollection
	 */
	public function getRouteCollection()
	{
		return $this->_collection;
	}

	/**
	 * Set the URL prefix to append to all routes in this collection
	 *
	 * @param string $prefix The prefix to use.
	 *
	 * @return RouteCollection
	 */
	public function setPrefix($prefix)
	{
		$this->_prefix = $prefix;

		return $this;
	}

	/**
	 * Get the URL prefix set for this collection.
	 *
	 * @return string The prefix set for this collection (if any)
	 */
	public function getPrefix()
	{
		return $this->_prefix;
	}

	/**
	 * Make this collection a child of another one.
	 *
	 * @param string $collectionName The name of the parent to attach this
	 *                               collection to.
	 */
	public function setParent($collectionName)
	{
		$this->_parent = $collectionName;

		return $this;
	}

	/**
	 * Gets the name of the parent of this collection (if any)
	 *
	 * @return string The name of the parent collection (if one has been set)
	 */
	public function getParent()
	{
		return $this->_parent;
	}
}