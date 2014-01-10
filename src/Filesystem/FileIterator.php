<?php

namespace Message\Cog\Filesystem;

/**
* An iterator that injects Message\Cog\Filesystem\File into Symfony's finder component and replaces
* SplFileInfo with our own class.
*
* @author  James Moss <james@message.co.uk>
*/
class FileIterator extends \IteratorIterator
{
	/**
	 * Ensures that a Message\Cog\Filesystem\File instance is returned from the finder.
	 *
	 * @return File A file object representing the file.
	 */
	public function current()
	{
		$file = parent::current();

		return new File((string) $file);
	}
}