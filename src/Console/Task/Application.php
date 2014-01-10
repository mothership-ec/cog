<?php

namespace Message\Cog\Console\Task;

use Message\Cog\Console\Application as BaseApplication;

/**
* Stops the default help and list commands from working when executing a task
*/
class Application extends BaseApplication
{
	protected function getDefaultCommands()
	{
		return array();
	}
}