<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\Container as ServiceContainer;
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
		$console = Factory::create();
		$this->_services['app.console'] = function() use ($console) {
			return $console;
		};

		$input = new ArgvInput();
		if($env = $input->getParameterOption(array('--env', '-e'), '')) {
			$this->_services['environment']->set($env);
		}

		// TODO: Let the application set the name / version
		$console = $this->_services['app.console'];
		$console->setName('Cog Console');
		//$console->setVersion(1);
		$console->run();
	}
}