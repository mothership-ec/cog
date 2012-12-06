<?php

namespace Message\Cog\Test\Service;

use Message\Cog\Service\ContainerInterface;
use Closure;

/**
 * Identifier for Shared Services, used in the `FauxContainer` implementation of
 * `Service\ContainerInterface`.
 *
 * When calling `share()` on `FauxContainer` an instance of this class is
 * returned, so unit tests can check for an instance of this class to test that
 * a service was defined as shared.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SharedServiceIdentifier
{
	protected $_callable;
	protected $_container;

	/**
	 * Constructor.
	 *
	 * @param Closure $callable The service definition callable
	 */
	public function __construct(Closure $callable, ContainerInterface $container)
	{
		$this->_callable  = $callable;
		$this->_container = $container;
	}

	/**
	 * Invoke the callable and return it when this wrapper class is invoked.
	 *
	 * @return mixed The value returned by the callable
	 */
	public function __invoke()
	{
		$callable = $this->_callable;

		return $callable($this->_container);
	}
}