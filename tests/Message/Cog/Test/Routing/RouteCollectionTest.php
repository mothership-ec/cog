<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\Module\ReferenceParser;
use Message\Cog\Test\Module;

use Message\Cog\Routing\Router;
use Message\Cog\Routing\Route;
use Message\Cog\Routing\RouteCollection;
use Message\Cog\Routing\RequestContext;


class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
	const ROUTE_CONTROLLER_REFERENCE = 'Message:CMS::ClassName#viewMethod';

	protected $_referenceParser;

	const DEFAULT_VENDOR = 'Message';
	const DEFAULT_MODULE = 'Cog';

	public function setUp()
	{
		$this->_modulePaths['UniformWares\\CustomModuleName'] = __DIR__.'/fixtures/module/example';

		$fnsUtility = $this->getMockBuilder('Message\\Cog\\Functions\\Utility')
			->disableOriginalConstructor()
			->getMock();

		// Set the default/traced vendor and module
		$fnsUtility
			->expects($this->any())
			->method('traceCallingModuleName')
			->will($this->returnValue('Message\\Cog'));

		$this->parser = new ReferenceParser(
			new Module\FauxLocator($this->_modulePaths),
			$fnsUtility
		);

		$this->collection = new RouteCollection($this->parser);
	}

	public function testAddingRoute()
	{
		$result = $this->collection->add('test.route', '/home', '::Controller#home');

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\Route', $result);
	}

	public function testGettingUnderlyingRouteCollection()
	{
		$result = $this->collection->getRouteCollection();

		$this->assertInstanceOf('\\Symfony\\Component\\Routing\\RouteCollection', $result);
	}

	public function testGettingSettingPrefix()
	{
		$result = $this->collection->setPrefix('/admin');

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\RouteCollection', $result);
		$this->assertSame($this->collection->getPrefix(), '/admin');
	}

	public function testGettingSettingParent()
	{
		$result = $this->collection->setParent('orders');

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\RouteCollection', $result);
		$this->assertSame($this->collection->getParent(), 'orders');
	}
	
}