<?php

namespace Message\Cog\Test\ImageResize;

use Message\Cog\ImageResize\Resize;


class ResizeTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$imagine = $this->getMock('\\Imagine\\Gd\\Imagine');
		$generator = $this->getMock('\\Message\\Cog\\Routing\\UrlGenerator');

		$this->resize = new Resize($imagine, $generator);
	}

	public function testGeneratingUrl()
	{
		
	}
	
}