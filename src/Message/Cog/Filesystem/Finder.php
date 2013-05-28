<?php

namespace Message\Cog\Filesystem;

use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
* A wrapper for Cog around Symfony's Finder component
* http://symfony.com/doc/master/components/finder.html
*
* @author  James Moss <james@message.co.uk>
*/
class Finder extends SymfonyFinder
{
	/**
	 * Before the finder returns the iterator, inject our own FileIterator which ensures
	 * a File object is always returned.
	 *
	 * @return FileIterator The iterator which injects a File object
	 */
	public function getIterator()
	{
		$parent = parent::getIterator();
		$iterator = new FileIterator($parent);
		
		return $iterator;
	}
}