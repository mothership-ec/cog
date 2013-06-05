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
	 * This dispatches the master request and sends it to the client, and then
	 * terminates.
	 */
	public function run()
	{
		// @todo can we move this somewhere better? it needs to happen here though :(
		$this->_services['event.dispatcher']->addSubscriber(
			new \Symfony\Component\HttpKernel\EventListener\RouterListener($this->_services['routing.matcher'])
		);

		$response = $this->_services['http.kernel']->handle($this->_services['http.request.master']);

		$response->send();

		$this->_services['http.kernel']->terminate($this->_services['http.request.master'], $response);
	}
}