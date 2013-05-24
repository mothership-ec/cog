<?php

namespace Message\Cog\Filesystem;

/**
* An iterator that injects Message\Cog\Filesystem\File into Symfony's finder component
*/
class File extends \SplFileInfo
{
	/**
	 * Calculates the md5 checksum of a file.
	 *
	 * @return string 	the file's md5 checksum.
	 */
	public function getChecksum()
	{
		return md5_file($this->getRealPath());
	}
}