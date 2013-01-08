<?php

namespace Message\Cog\Bootstrap;

/**
 * Bootstrap interface for registering routes to the router.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface RoutesInterface extends BootstrapInterface
{
	/**
	 * Register routes to the given router.
	 *
	 * The router is not type hinted because this would mean we would have to
	 * type hint it in every class that uses this interface which is
	 * unmanageable.
	 *
	 * We can assume that `$router` is an instance of
	 * `\Message\Cog\Routing\RouterInterface`.
	 *
	 * @param object $router The router
	 */
	public function registerRoutes($router);
}