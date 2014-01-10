<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\ContainerInterface;

/**
 * Web context loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Web implements ContextInterface
{
	protected $_services;

	/**
	 * Constructor. This is run before any modules are loaded, so we can
	 * initialise the web request as a service here.
	 *
	 * The master request is instantiated and set on the service container,
	 * along with the current request context.
	 *
	 * @param ContainerInterface $container The service container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->_services = $container;

		$this->_services['http.request.master'] = $this->_services->share(function() {
			return \Message\Cog\HTTP\Request::createFromGlobals();
		});

		$this->_services['request'] = $this->_services->share(function($c) {
			return $c['http.request.master'];
		});

		$this->_services['http.fragment_handler']->setRequest($this->_services['request']);

		$this->_services['http.request.context'] = function($c) {
			$context = new \Message\Cog\Routing\RequestContext;
			$context->fromRequest(isset($c['request']) ? $c['request'] : $c['http.request.master']);

			return $context;
		};
	}

	/**
	 * Run a web request.
	 *
	 * This dispatches the master request and sends it to the client, and then
	 * terminates.
	 */
	public function run()
	{
		$response = $this->_services['http.kernel']->handle($this->_services['http.request.master']);

		$response->send();

		$this->_services['http.kernel']->terminate($this->_services['http.request.master'], $response);
	}
}