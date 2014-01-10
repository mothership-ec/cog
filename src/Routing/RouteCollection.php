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
	const DEFAULT_PRIORITY = 0;

	protected $_name;
	protected $_collection;
	protected $_prefix = '';
	protected $_parent;
	protected $_priority;

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
		if (!is_callable($controller)) {
			$reference  = $this->_referenceParser->parse($controller);
			$controller = $reference->getSymfonyLogicalControllerName();
		}

		$defaults = array(
			'_controller' => $controller,
		);
		
		$route = new Route($url, $defaults);

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
	 * Set priority of a route collection.
	 * A priority can only be set on a root route collection (without parent)
	 *
	 * @param 	int $priority 	The value the priority will be set to
	 * @throws 	\Exception 		If somebody tries to set a priority on a nested
	 *							route collection
	 * @return 	RouteCollection
	 */
	public function setPriority($priority)
	{
		if(!is_null($this->getParent())) {
			throw new \Exception('You cannot set a priority on a nested RouteCollection.');
		}

		$this->_priority = (int)$priority;

		return $this;
	}

	/**
	 * Get priority of a route collection.
	 *
	 * @return int|false 	The $_priority or, if not set, false
	 * @throws \Exception 	If somebody tries to get a priority on a nested
	 *						route collection
	 */
	public function getPriority()
	{
		if(!is_null($this->getParent())) {
			throw new \Exception('Nested RouteCollections cannot have a priority.');
		}

		return (!is_null($this->_priority) ? $this->_priority : self::DEFAULT_PRIORITY);
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