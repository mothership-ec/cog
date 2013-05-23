<?php

namespace Message\Cog\Filesystem;

use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
* A wrapper for Cog around Symfony's Finder component
* http://symfony.com/doc/master/components/finder.html
*/
class Finder extends SymfonyFinder
{
	public function getIterator()
	{
		$parent = parent::getIterator();
		$iterator = new FileIterator($parent);
		
		return $iterator;
	}
}