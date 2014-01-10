<?php

namespace Message\Cog\Test\Console\Task;

use Message\Cog\Console\Task\Task;

class FauxTask extends Task
{
	public function getName()
	{
		return 'faux';
	}

	public function process()
	{
	
	}
}