<?php

namespace Message\Cog\Test\ImageResize;

use Message\Cog\ImageResize\Resize;


class ResizeTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$imagine = $this->getMock('\\Imagine\\Image\\ImagineInterface');
		$generator = $this->getMockBuilder('\\Message\\Cog\\Routing\\UrlGenerator')
		    ->disableOriginalConstructor()
		    ->getMock();

		$this->resize = new Resize($imagine, $generator);
	}

	public function testGeneratingUrl()
	{
		$url = $this->resize->generateUrl('files/somefile.jpg', 300, 500);

		var_dump($url);
	}
	
}