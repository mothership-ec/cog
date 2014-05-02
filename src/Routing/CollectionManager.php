<?php

namespace Message\Cog\Routing;

use Message\Cog\Module\ReferenceParserInterface;

use Message\Cog\Functions\Iterable;

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
		// first check validity of _collections
		$this->_checkCollectionsValidity();

		// Firstly, find collections that have a parent set and add them to that
		// parent.
		$hierarchy   = $this->_getCollectionHierarchy();
		$collections = array();

		// Sort heirarchy by most deeply nested first
		uasort($hierarchy, function($a, $b) {
			return count($b) - count($a);
		});

		// Order the collections using the same ordering
		foreach ($hierarchy as $name => $val) {
			$collections[$name] = $this->_collections[$name];
		}

		$baseCollections = array();
		foreach($collections as $name => $collection) {
			// Save the parent route collection names onto the routes.
			$this->_saveCollectionNameToRoutes($collection->getRouteCollection(), $hierarchy[$name]);

			// Save collections without parents as we have to loop over them
			// later.
			if(!$collection->getParent()) {
				$baseCollections[$name] = $collection;
				continue;
			}

			// Get the parent
			$parent           = $collection->getParent();
			$parentCollection = $this->_collections[$parent]->getRouteCollection();

			$symfonyCollection = $this->_convertCollectionToSymfonyCollection($collection);

			// Add it to Symfony's underlying RouteCollection
			$parentCollection->addCollection($symfonyCollection);
			$this->_collections[$parent]->getRouteCollection()->addCollection($symfonyCollection);
		}

		// Sort all base collections according to their priority
		uasort($baseCollections, function($a, $b) {
			return $b->getPriority() - $a->getPriority();
		});

		// Create an empty route collection, all the others get added to this.
		$root           = new RouteCollection($this->_referenceParser);
		$rootCollection = $root->getRouteCollection();

		foreach($baseCollections as $name => $collection) {
			$symfonyCollection = $this->_convertCollectionToSymfonyCollection($collection);

			$rootCollection->addCollection($symfonyCollection);
		}

		return $root;
	}

	protected function _checkCollectionsValidity() {
		foreach($this as $name => $collection) {
			$parent = $collection->getParent();

			if(!$parent) {
				continue;
			}

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
		}
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
			$this->_collections[$name] = new RouteCollection($this->_referenceParser, $name);
		}

		return $exists;
	}

	/**
	 * Sets route collection names on a route (if they arent already set)
	 *
	 * @param  RouteCollection    $collection A collection to set the parent names on
	 * @param  array $hierarchy  An array of the parent collection names
	 *
	 * @return void
	 */
	protected function _saveCollectionNameToRoutes($collection, $parents)
	{
		foreach($collection->all() as $route) {
			if(!$route->hasDefault('_route_collections')) {
				$route->setDefault('_route_collections', $parents);
			}

		}
	}

	/**
	 * Gets the parent collection names for each route collection.
	 *
	 * @return array An array where the key is the collection name and the
	 *               value is an array of the parent collection names.
	 */
	protected function _getCollectionHierarchy()
	{
		$parents = array();
		foreach ($this as $collectionName => $collection) {
			$parents[$collectionName] = $collection->getParent();
		}

		$tree = Iterable::toTree($parents);
		$keys = Iterable::arrayKeysMultidimensional($tree);

		$result = array();
		foreach($keys as $key) {
			$result[$key] = array_merge((array)Iterable::getParentsFromKey($key, $tree), array($key));
		}

		return $result;
	}

	/**
	 * Convert a Cog `RouteCollection` to a Symfony `RouteCollection`, copying
	 * across all properties such as host, schemes, methods, defaults &
	 * requirements.
	 *
	 * @param  RouteCollection $collection The Cog route collection
	 *
	 * @return \Symfony\Component\Routing\RouteCollection
	 */
	protected function _convertCollectionToSymfonyCollection(RouteCollection $collection)
	{
		$symfonyCollection = $collection->getRouteCollection();

		$symfonyCollection->addPrefix($collection->getPrefix());

		if ($host = $collection->getHost()) {
			$symfonyCollection->setHost($host);
		}

		if ($schemes = $collection->getSchemes()) {
			$symfonyCollection->setSchemes($schemes);
		}

		if ($methods = $collection->getMethods()) {
			$symfonyCollection->setMethods($methods);
		}

		if ($defaults = $collection->getDefaults()) {
			$symfonyCollection->addDefaults($defaults);
		}

		if ($requirements = $collection->getRequirements()) {
			$symfonyCollection->addRequirements($requirements);
		}

		return $symfonyCollection;
	}
}