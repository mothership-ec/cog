<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Console\Factory;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

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
	 * @param array|null 		 $arguments Arguments to pass into the Console component
	 *
	 * @todo Change the environment earlier if possible. Can we make the context
	 * run something before even Cog is bootstrapped?
	 */
	public function __construct(ContainerInterface $container, array $arguments = null)
	{
		$this->_services = $container;

		$console = Factory::create();
		$this->_services['app.console'] = $this->_services->share(function() use ($console) {
			return $console;
		});

		if(null === $arguments) {
			$arguments = $_SERVER['argv'];
		}

		$input = new ArgvInput($arguments);
		if($env   = $input->getParameterOption(array('--env'), '')) {
			$this->_services['environment']->set($env);
		}

		// Setup a fake request context
		$this->_services['http.request.context'] = function($c) {
			$context = new \Message\Cog\Routing\RequestContext;

			return $context;
		};
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