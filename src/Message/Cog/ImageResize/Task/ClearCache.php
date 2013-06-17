<?php

namespace Message\Cog\ImageResize\Task;

use Message\Cog\Console\Task\Task;
use Message\Cog\Filesystem\File;

use Symfony\Component\Console\Input\InputArgument;

class ClearCache extends Task
{
	public function process()
	{
		$path = new File($this->get('image.resize')->getCachePath());

		$fs = $this->get('filesystem.finder');

		foreach($fs->depth('== 0')->in($path->getRealPath()) as $file) {
			$this->get('filesystem')->remove($file->getRealPath());
		}

		return '<info>'.$path.' has been emptied.</info>';
	}
}