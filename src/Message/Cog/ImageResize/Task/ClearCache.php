<?php

namespace Message\Cog\ImageResize\Task;

use Message\Cog\Console\Task\Task;

class ClearCache extends Task
{
	public function process()
	{
		$path = $this->get('image.resize')->getCachePath();

		$fs = $this->get('filesystem.finder');

		foreach($fs->in($path) as $file) {
			var_dump($file->getRealPath());
			//§$this->get('filesystem')->remove($file->getRealPath());
		}

		return $path;
	}
}