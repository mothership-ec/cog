<?php

namespace Message\Cog\ImageResize\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router->add('imageresize.cache', '/resize/{url}', '::Controller#index')
			->setRequirement('url', '.+');
	}
}