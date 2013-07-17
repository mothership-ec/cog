<?php

namespace Message\Cog\ImageResize\Bootstrap;

use Message\Cog\ImageResize\Task;
use Message\Cog\Bootstrap\TasksInterface;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		$tasks->add(new Task\ClearCache('imageresize:clear:cache'), 'Deletes all resized images.');
	}
}