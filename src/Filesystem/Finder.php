<?php

namespace Message\Cog\Filesystem;

use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
* A wrapper for Cog around Symfony's Finder component
* 
* @see  http://symfony.com/doc/master/components/finder.html
*
* @author  James Moss <james@message.co.uk>
*/
class Finder extends SymfonyFinder
{
	/**
	 * Before the finder returns the iterator, inject our own FileIterator which ensures
	 * a File object is always returned.
	 * 
	 * Iterators stack up, so this code iterates the $parent iterator - but we want this. 
	 * Our FileIterator is the last in the chain so when it iterates over the
	 * elements from $parent it returns them all as a Cog\File object.
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