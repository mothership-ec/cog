<?php

namespace Message\Cog\ImageResizer\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router->add('imageresizer.cache', '/cache/build/{url}', '::Controller#index')
			->setRequirement('url', '.+');
	}
}