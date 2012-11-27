<?php

namespace Message\Cog\Service;

/**
 * ContainerAwareInterface should be implemented by classes that depend on a/the
 * service container. A standard implementation of this exists as ContainerAware.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ContainerAwareInterface
{
	/**
	 * Sets the service container on this object.
	 *
	 * @param ContainerInterface $container The service container
	 */
	public function setContainer(ContainerInterface $container);
}