<?php

namespace Message\Cog\Service;

/**
 * A standard implementation of ContainerAwareInterface.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class ContainerAware implements ContainerAwareInterface
{
	protected $_services;

	/**
	 * Sets the service container on this object.
	 *
	 * @param ContainerInterface $container The service container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}
}