<?php

namespace Message\Cog\Filesystem;

/**
* An iterator that injects Message\Cog\Filesystem\File into Symfony's finder component and replaces
* SplFileInfo with our own class
*/
class FileIterator extends \IteratorIterator
{
	public function current()
	{
		$file = parent::current();
		
		return new File($file->getRealPath());
	}
}