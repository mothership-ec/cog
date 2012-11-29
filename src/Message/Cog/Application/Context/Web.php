<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\Container as ServiceContainer;

/**
 * Web context loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Web implements ContextInterface
{
	protected $_services;

	/**
	 * Constructor. This sets the service container.
	 */
	public function __construct()
	{
		$this->_services = ServiceContainer::instance();
	}

	/**
	 * Run a web request.
	 *
	 * This creates the master request, adds it to the service container and
	 * dispatches it. Then the response is sent.
	 */
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