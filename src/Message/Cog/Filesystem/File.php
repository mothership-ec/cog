<?php

namespace Message\Cog\Filesystem;

/**
* An iterator that injects Message\Cog\Filesystem\File into Symfony's finder component
*/
class File extends \SplFileInfo
{
	public function getChecksum()
	{
		return md5_file($this->getRealPath());
	}
}