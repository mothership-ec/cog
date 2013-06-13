<?php

namespace Message\Cog\ImageResize\Task;

use Message\Cog\Console\Task\Task;

class ClearCache extends Task
{
	public function process()
	{
		return '<info>Successfully ran `imageresize:clear:cache`</info>';
	}
}