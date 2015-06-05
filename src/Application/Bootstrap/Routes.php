<?php

namespace Message\Cog\Application\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router->add('cog.module.file.get', '/cogules/{fileRef}', 'Message:Cog::Application:Controller:Module#getFile')
			->setRequirement('fileRef', '.+')
			->setMethod('GET');
	}
}