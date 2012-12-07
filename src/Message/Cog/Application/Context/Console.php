<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Console\Factory;

use Symfony\Component\Console\Input\ArgvInput;

/**
 * Console context loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author James Moss <james@message.co.uk>
 */
class Console implements ContextInterface
{
	protected $_services;

	/**
	 * Constructor. This is run before any modules are loaded, so we can
	 * initialise the console here.
	 *
	 * @param ContainerInterface $container The service container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->_services = $container;

		$console = Factory::create();
		$this->_services['app.console'] = $this->_services->share(function() use ($console) {
			return $console;
		});

		// Set the environment from the CLI option, if defined
		$input = new ArgvInput();
		if($env = $input->getParameterOption(array('--env', '-e'), '')) {
			$this->_services['environment']->set($env);
		}
	}

	/**
	 * Run a web request.
	 *
	 * This creates the master request, adds it to the service container and
	 * dispatches it. Then the response is sent.
	 *
	 * @todo Let the application set the name / version
	 */
	public function run()
	{
		$console = $this->_services['app.console'];
		$console->setName('Cog Console');
		//$console->setVersion(1);
		$console->run();
	}
}