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
		return new ArrayIterator($this->_collections);
	}

	public function _checkCollection($name, $create = false)
	{
		$exists = isset($this->_collections[$name]);


		if(!$exists && $create) {
			$this->_collections[$name] = new RouteCollection($this->_referenceParser);
		}

		return $exists;
	}
}