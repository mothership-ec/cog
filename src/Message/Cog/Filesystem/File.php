<?php

namespace Message\Cog\Filesystem;

/**
* An extension of SplFileInfo that enables us to add our own customisations.
*
* @author  James Moss <james@message.co.uk>
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