<?php

namespace Message\Cog\Bootstrap;

/**
 * Bootstrap interface for registering services to the service container.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ServicesInterface extends BootstrapInterface
{
	/**
	 * Register services to the given service container.
	 *
	 * The service container is not type hinted because this would mean we would
	 * have to type hint it in every class that uses this interface which is
	 * unmanageable.
	 *
	 * We can assume that `$serviceContainer` is an instance of
	 * `\Message\Cog\Service\ContainerInterface`.
	 *
	 * @param object $serviceContainer The service container
	 */
	public function registerServices($serviceContainer);
}