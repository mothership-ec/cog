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
	 * @param ContainerInterface $container The service container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->_services = $container;

		$this->_services['http.request.master'] = $this->_services->share(function() {
			return \Message\Cog\HTTP\Request::createFromGlobals();
		});
	}

	/**
	 * Run a web request.
	 *
	 * This creates the master request, adds it to the service container and
	 * dispatches it. Then the response is sent.
	 */
	public function run()
	{
		$this->_services['event.dispatcher']->addSubscriber(
			new \Symfony\Component\HttpKernel\EventListener\RouterListener($this->_services['routing.matcher'])
		);

		$response = $this->_services['http.kernel']->handle($this->_services['http.request.master']);

		$response->send();

		$this->_services['http.kernel']->terminate($this->_services['http.request.master'], $response);
	}
}