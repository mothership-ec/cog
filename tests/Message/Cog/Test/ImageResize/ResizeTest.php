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
		    ->setMethods(array('generate'))
		    ->getMock();

		$generator
		    ->expects($this->any())
		    ->method('generate')
		    ->will($this->returnValue('/resizer/-'));

		$this->resize = new Resize($imagine, $generator, 'faux_route_name', 'somesalt');
	}

	public function testGeneratingUrl()
	{
		$url = $this->resize->generateUrl('files/somefile.jpg', 300, 500);

		$this->assertSame('/resizer/files/somefile_300x500-334ce4.jpg', $url);
	}
	
}