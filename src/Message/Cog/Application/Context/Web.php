<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\ContainerAware;

class Web extends ContainerAware implements ContextInterface
{
	public function run()
	{
		$this->_services['http.request.master'] = $this->_services->share(function() {
			return \Message\Cog\HTTP\Request::createFromGlobals();
		});

		$this->_services['http.dispatcher']
			->handle($this->_services['http.request.master'])
			->send();
	}
}