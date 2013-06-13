<?php

namespace Message\Cog\Console;

/**
* Stops the default help and list commands from working when executing a task
*/
class TaskApplication extends Application
{
	protected function getDefaultCommands()
	{
		return array();
	}
}