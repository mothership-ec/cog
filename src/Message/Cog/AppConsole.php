<?php

namespace Message\Cog;

use Symfony\Component\Console\Input\ArgvInput;

/**
* AppConsole
*
* Controls startup of the commandline version of Mothership
*/
class AppConsole extends App
{
	public function __construct()
	{
		parent::__construct();
		$this->setupConsole();
		$this->setupFrameworkServices();
	}

	public function setupConsole()
	{
		$services = Service\Container::instance();
		$console = Console\Factory::create();
		$services['app.console'] = function() use ($console) {
			return $console;
		};

		$input = new ArgvInput();
		if($env = $input->getParameterOption(array('--env', '-e'), '')) {
			$services['environment']->set($env);
		}
	}

	public function run()
	{
		// TODO: Make Mothership\Core set the console name / version
		$console = Service\Container::get('app.console');
		$console->setName('Cog Console');
		$console->setVersion(Service\Container::get('config')->merchant->name);
		$console->run();
	}
}