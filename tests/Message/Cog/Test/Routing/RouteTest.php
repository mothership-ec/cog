<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\ReferenceParser;
use Message\Cog\Test\Module;

use Message\Cog\Routing\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
	public function testThatDefaultFormatIsHtml()
	{
		$route = new Route('/blog');
		$this->assertSame('html', $route->getDefault('_format'));
	}

	public function testSettingScheme()
	{
		$route = new Route('/blog');
		$route->setScheme('https');
		$this->assertSame('https', $route->getRequirement('_scheme'));
	}

	public function testSettingFormat()
	{
		$route = new Route('/blog');
		$route->setFormat('json');
		$this->assertSame('json', $route->getDefault('_format'));
	}

	public function testSettingMethod()
	{
		$route = new Route('/blog');
		$route->setMethod('PUT');
		$this->assertSame('PUT', $route->getRequirement('_method'));
	}

	public function testSettingOptional()
	{
		$route = new Route('/blog/{blogID}/comment/{commentID}');
		$route->setOptional(array('blogID', 'commentID'));

		$this->assertSame('.*', $route->getRequirement('blogID'));
		$this->assertSame('.*', $route->getRequirement('commentID'));

		$route = new Route('/order/{orderID}');
		$route->setOptional('orderID');

		$this->assertSame('.*', $route->getRequirement('orderID'));
	}
}