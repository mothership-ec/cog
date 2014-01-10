<?php

namespace Message\Cog\Test\Filesystem;

use Message\Cog\Filesystem\Finder;

class FinderTest extends \PHPUnit_Framework_TestCase
{
	public function testFinderReturnsFileObject()
	{
		$path = __DIR__.'/fixtures/tmp';
		$finder = new Finder;

		foreach($finder->in($path) as $file) {
			$this->assertInstanceOf('\\Message\\Cog\\Filesystem\\File', $file);
		}
	}
}