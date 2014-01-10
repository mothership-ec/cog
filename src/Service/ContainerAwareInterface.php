<?php

namespace Message\Cog\Service;

/**
 * Interface for classes that are aware of the service container. Use this
 * sparingly.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ContainerAwareInterface
{
	/**
	 * Sets the service container on this class.
	 *
	 * @param ContainerInterface $container The service container
	 */
	public function setContainer(ContainerInterface $container);
}