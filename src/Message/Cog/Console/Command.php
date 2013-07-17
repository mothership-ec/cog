<?php

namespace Message\Cog\Console;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Our wrapper around Symfony's Console component.
 */
class Command extends BaseCommand implements ContainerAwareInterface
{
	/**
	 * {inheritDoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Gets a service from the container
	 *
	 * @param  string $name The name of the service to retrieve
	 *
	 * @return mixed       The service (if it exists)
	 */
	public function get($name)
	{
		return $this->_services[$name];
	}
}