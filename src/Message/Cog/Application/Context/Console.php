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
	 *
	 * @todo Change the environment earlier if possible. Can we make the context
	 * run something before even Cog is bootstrapped?
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->_services = $container;

		$console = Factory::create();
		$this->_services['app.console'] = $this->_services->share(function() use ($console) {
			return $console;
		});

		// Set the environment from the CLI option, if defined
		$input = new ArgvInput(null, $console->getDefinition());
		if($env = $input->getOption(Factory::ENV_OPT_NAME)) {
			$this->_services['environment']->set(trim($env));
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