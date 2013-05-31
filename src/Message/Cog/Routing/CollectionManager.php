<?php

namespace Message\Cog\Routing;

use Message\Cog\ReferenceParserInterface;

class CollectionManager implements \ArrayAccess, \IteratorAggregate
{
	protected $_collections = array();
	protected $_referenceParser;
	protected $_defaultCollection;

	/**
	 * Constructor.
	 *
	 * @api
	 */
	public function __construct(ReferenceParserInterface $referenceParser, $defaultCollection = 'default')
	{
		$this->_referenceParser   = $referenceParser;
		$this->_defaultCollection = $defaultCollection;
	}

	public function add($name, $url, $controller)
	{
		$this->_checkCollection($this->_defaultCollection, true);

		return $this->_collections[$this->_defaultCollection]->add($name, $url, $controller);
	}

	public function getDefault()
	{
		return $this->_collections[$this->_defaultCollection];
	}

	public function offsetExists($offset)
	{
		return $this->_checkCollection($offset);
	}

	public function offsetGet($offset)
	{
		$this->_checkCollection($offset, true);

		return $this->_collections[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new \Exception(sprintf(
			'Collections cannot be set directly on %s. Use add() instead.',
			__CLASS__
		));
	}

	public function offsetUnset($offset)
	{
		throw new \Exception(sprintf(
			'Collections cannot be removed or unset from %s.',
			__CLASS__
		));
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_collections);
	}

	public function _checkCollection($name, $create = false)
	{
		$exists = isset($this->_collections[$name]);

		if(!$exists && $create) {
			$this->_collections[$name] = new RouteCollection($this->_referenceParser);
		}

		return $exists;
	}

	public function compileRoutes()
	{
		// mount collections that have parents set first
		$baseCollections = array();
		foreach($this as $name => $collection) {

			if(!$collection->getParent()) {
				$baseCollections[$name] = $collection;
				continue;
			}

			$parent = $collection->getParent();

			if($parent === $name) {
				throw new \RuntimeException(sprintf(
					'RouteCollection `%s` cannot be set as a parent of itself.',
					$name
				));
			}

			if(!isset($this->_collections[$parent])) {
				throw new \RuntimeException(sprintf(
					'Cannot add RouteCollection `%s` to `%s` as it does not exist.', 
					$name,
					$parent
				));
			}

			$prefix = $collection->getPrefix();
			$this->_collections[$parent]->getRouteCollection()->addCollection($collection->getRouteCollection(), $prefix);
		}

		// create an empty route collection, all the others get added to this.
		$base = new RouteCollection($this->_referenceParser);
		$baseCollection = $base->getRouteCollection();
		
		foreach($baseCollections as $name => $collection) {
			$baseCollection->addCollection($collection->getRouteCollection(), $collection->getPrefix());
		}

		return $base;
	}
}