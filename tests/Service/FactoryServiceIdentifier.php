<?php

namespace Message\Cog\Test\Service;

use Message\Cog\Service\ContainerInterface;
use Closure;

/**
 * Identifier for factory services, used in the `FauxContainer` implementation of
 * `Service\ContainerInterface`.
 *
 * When calling `factory()` on `FauxContainer` an instance of this class is
 * returned, so unit tests can check for an instance of this class to test that
 * a service was defined as a factory.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FactoryServiceIdentifier
{
	protected $_callable;
	protected $_invokeResult;
	protected $_container;

	/**
	 * Constructor.
	 *
     * @param Closure            $callable  The service definition callable
     * @param ContainerInterface $container The service container
	 */
	public function __construct($callable, ContainerInterface $container)
	{
		$this->_callable  = $callable;
		$this->_container = $container;
	}

	/**
	 * Invoke the callable and return it when this wrapper class is invoked.
	 *
	 * The first time this is called we save the result as `_invokeResult` so
	 * this service does actually get shared (rather than re-invoked).
	 *
	 * @return mixed The value returned by the callable
	 */
	public function __invoke()
	{
		if (!isset($this->_invokeResult)) {
			$callable = $this->_callable;
			$this->_invokeResult = $callable($this->_container);
		}

		return $this->_invokeResult;
	}
}
