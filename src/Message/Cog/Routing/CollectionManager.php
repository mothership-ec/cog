<?php

namespace Message\Cog\Routing;

use Message\Cog\ReferenceParserInterface;

/**
 * Manages groups of routes for use in the router. Groups are represented as
 * RouteCollections, this in turn is a decorator for Symfony's RouteCollection
 * class.
 *
 * This class is also capabale of compiling all the separate RouteCollections
 * into a single one which can be passed to the Router.
 */
class CollectionManager implements \ArrayAccess, \IteratorAggregate
{
	protected $_collections = array();
	protected $_referenceParser;
	protected $_defaultCollection;

	/**
	 * Constructor
	 *
	 * @param ReferenceParserInterface $referenceParser   A instance of Cog's reference parser
	 * @param string                   $defaultCollection The name to use for the default collection
	 *                                                    when add() is called directly on this class.
	 */
	public function __construct(ReferenceParserInterface $referenceParser, $defaultCollection = 'default')
	{
		$this->_referenceParser   = $referenceParser;
		$this->_defaultCollection = $defaultCollection;

		// create the default collection
		$this->_checkCollection($this->_defaultCollection, true);
	}

	/**
	 * Adds a Route to the default collection. This signature should match that
	 * of add() in Message\Cog\Routing\RouteCollection.
	 *
	 * @see Message\Cog\Routing\RouteCollection::add
	 *
	 * @param string $name       The name of the route. Must be unique.
	 * @param string $url        The URL pattern to match against.
	 * @param string $controller The controller/method to use when matched.
	 *
	 * @return Route The new route that has just been added.
	 */
	public function add($name, $url, $controller)
	{
		return $this->_collections[$this->_defaultCollection]->add($name, $url, $controller);
	}

	/**
	 * Gets the default collection.
	 *
	 * @return RouteCollection
	 */
	public function getDefault()
	{
		return $this->_collections[$this->_defaultCollection];
	}

	/**
	 * Check if a collection exists.
	 *
	 * @param  string $offset The collection to check
	 *
	 * @return boolean        Returns true if it exists, else false.
	 */
	public function offsetExists($offset)
	{
		return $this->_checkCollection($offset);
	}

	/**
	 * Get a collection by name. If it doesn't exist it will be created.
	 *
	 * @param  string $offset The collection to return.
	 *
	 * @return RouteCollection  The requested collection.
	 */
	public function offsetGet($offset)
	{
		$this->_checkCollection($offset, true);

		return $this->_collections[$offset];
	}

	/**
	 * Attempt to set a collection.
	 *
	 * @param  string $offset The name of the collection to set
	 * @param  mixed $value  The value to set it to.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		throw new \Exception(sprintf(
			'Collections cannot be set directly on %s. Use add() instead.',
			__CLASS__
		));
	}

	/**
	 * Remove a collection
	 *
	 * @param  string $offset The name of the collection to remove
	 *
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		throw new \Exception(sprintf(
			'Collections cannot be removed or unset from %s.',
			__CLASS__
		));
	}

	/**
	 * Iterator to allow looping over this class as if it was an array.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_collections);
	}

	/**
	 * Combines all the collections into a single collection as well as mounting
	 * collections to their parents (if they have them set).
	 *
	 * @return RouteCollection The collection that contains all others.
	 */
	public function compileRoutes()
	{
		// Firstly, find collections that have a parent set and add them to that
		// parent.
		$baseCollections = array();
		foreach($this as $name => $collection) {

			// Save collections without parents as we have to loop over them
			// later.
			if(!$collection->getParent()) {
				$baseCollections[$name] = $collection;
				continue;
			}

			// Get the name of the parent
			$parent = $collection->getParent();

			// A collection can't be it's own parent
			if($parent === $name) {
				throw new \RuntimeException(sprintf(
					'RouteCollection `%s` cannot be set as a parent of itself.',
					$name
				));
			}

			// Ensure that the parent exists
			if(!isset($this->_collections[$parent])) {
				throw new \RuntimeException(sprintf(
					'Cannot add RouteCollection `%s` to `%s` as it does not exist.',
					$name,
					$parent
				));
			}

			// Get prefix we want to use and the collection we need to add to
			$prefix           = $collection->getPrefix();
			$parentCollection = $this->_collections[$parent]->getRouteCollection();

			$symfonyCollection = $collection->getRouteCollection();
			$symfonyCollection->addPrefix($prefix);

			// Add it to Symfony's underlying RouteCollection
			$parentCollection->addCollection($symfonyCollection);
		}

		// Create an empty route collection, all the others get added to this.
		$root           = new RouteCollection($this->_referenceParser);
		$rootCollection = $root->getRouteCollection();

		foreach($baseCollections as $name => $collection) {
			$symfonyCollection = $collection->getRouteCollection();
			$symfonyCollection->addPrefix($collection->getPrefix());

			$rootCollection->addCollection($symfonyCollection);
		}

		return $root;
	}

	/**
	 * Checks to see if a collection exists. Optionally it can create it if it doesn't already exist.
	 *
	 * @param  string  $name   The name of the collection to check.
	 * @param  boolean $create If set to true the collection will be created (if it doesnt already exist)
	 *
	 * @return boolean         Returns true if the collection exists, false otherwise.
	 */
	protected function _checkCollection($name, $create = false)
	{
		$exists = isset($this->_collections[$name]);

		if(!$exists && $create) {
			$this->_collections[$name] = new RouteCollection($this->_referenceParser);
		}

		return $exists;
	}
}