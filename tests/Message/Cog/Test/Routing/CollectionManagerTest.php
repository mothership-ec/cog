<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\Module\ReferenceParser;
use Message\Cog\Test\Module;

use Message\Cog\Routing\CollectionManager;
use Message\Cog\Routing\Route;
use Message\Cog\Routing\RouteCollection;

use Message\Cog\Routing\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class CollectionManagerTest extends \PHPUnit_Framework_TestCase
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
			->will($this->returnValue(self::DEFAULT_VENDOR . '\\' . self::DEFAULT_MODULE));

		$this->_referenceParser = new ReferenceParser(
			new Module\FauxLocator($this->_modulePaths),
			$fnsUtility
		);

		$this->_collection = new CollectionManager($this->_referenceParser);
	}


	public function testAddingRoute()
	{
		$result = $this->_collection->add('test.route', '/admin', '::Controller#admin');

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\Route', $result);
	}

	public function testGettingDefaultCollection()
	{
		$result = $this->_collection->getDefault();

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\RouteCollection', $result);
	}

	public function testOffsetExists()
	{
		$this->assertFalse($this->_collection->offsetExists('asd98skdnf3sdfsdf'));

		// create a new collection by accessing it
		$this->_collection->offsetGet('admin');
		$this->assertTrue($this->_collection->offsetExists('admin'));
	}

	public function testOffsetGet()
	{
		$result = $this->_collection->offsetGet('orders');

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\RouteCollection', $result);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testOffsetSet()
	{
		$this->_collection['joe'] = 'dude';
	}

	/**
	 * @expectedException \Exception
	 */
	public function testOffsetUnset()
	{
		unset($this->_collection['ting']);
	}

	public function testIterating()
	{
		$result = $this->_collection->getIterator();

		$this->assertInstanceOf('\\Traversable', $result);
	}

	public function testCompilingRoutes()
	{
		$this->_collection->add('core.homepage', '/', '::Controller:Test#login');
		$this->_collection['orders']->setParent('admin')->setPrefix('/orders');
		$this->_collection['orders']->add('core.bob', '/another', '::Controller:Test#another');
		$this->_collection['admin']->setPrefix('/admin')->add('core.more', '/more', '::Controller:Test#more');

		$result = $this->_collection->compileRoutes();

		$this->assertInstanceOf('\\Message\\Cog\\Routing\\RouteCollection', $result);
	}

	public function testCompilingRouteCollectionNames()
	{
		$route1 = $this->_collection->add('core.homepage', '/', '::Controller:Test#login');
		$this->_collection['orders']->setParent('admin')->setPrefix('/orders');
		$route2 = $this->_collection['orders']->add('core.bob', '/another', '::Controller:Test#another');
		$route3 = $this->_collection['shipping']->setParent('refunds')->add('shipping.list', '/shipping', '::Controller:Shipping#another');
		$this->_collection['admin']->setPrefix('/admin')->add('core.more', '/more', '::Controller:Test#more');
		$this->_collection['refunds']->setParent('orders')->add('refunds.list', '/refunds', '::Controller:Refunds#another');
		$route4 = $this->_collection['gifts']->setParent('refunds')->add('gifts.list', '/gifts', '::Controller:Shipping#another');

		$this->_collection->compileRoutes();

		$defaults = $route1->getDefaults();
		$this->assertSame($defaults['_route_collections'], array('default'));

		$defaults = $route2->getDefaults();
		$this->assertSame($defaults['_route_collections'], array('admin', 'orders'));

		$defaults = $route3->getDefaults();
		$this->assertSame($defaults['_route_collections'], array('admin', 'orders', 'refunds', 'shipping'));

		$defaults = $route4->getDefaults();
		$this->assertSame($defaults['_route_collections'], array('admin', 'orders', 'refunds', 'gifts'));
	}

	public function testUsingPriority()
	{
		$this->_collection['first']->add('first', '/', function() {
			return true;
		});
		
		$this->_collection['second']->add('second', '/', function() {
			return false;
		});

		$matcher1 = new UrlMatcher($this->_collection->compileRoutes()->getRouteCollection(), new RequestContext());
		$result = $matcher1->match('/');
		$this->assertEquals($result['_route'], 'first');

		$this->_collection['first']->setPriority(-900);

		$matcher2 = new UrlMatcher($this->_collection->compileRoutes()->getRouteCollection(), new RequestContext());
		$result = $matcher2->match('/');
		$this->assertEquals($result['_route'], 'second');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testSettingSelfAsParent()
	{
		$this->_collection['orders']
			->setParent('orders')
			->add('core.bob', '/another', '::Controller:Test#another');

		$this->_collection->compileRoutes();
	}

	/**
	 * @expectedException \Exception
	 */
	public function testSettingNonexistantAsParent()
	{
		$this->_collection['orders']
			->setParent('90ujci034hksnfd')
			->add('core.bob', '/another', '::Controller:Test#another');

		$this->_collection->compileRoutes();
	}

	
}