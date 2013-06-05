<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\ReferenceParser;
use Message\Cog\Test\Module;
use Message\Cog\Test\Service\FauxContainer;

use Message\Cog\Routing\CollectionManager;
use Message\Cog\Routing\EventListener;
use Message\Cog\Routing\RouteCollection;

use Message\Cog\Test\Event\FauxDispatcher;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{
	public function testSubscribedEvents()
	{
		$subscriptions = EventListener::getSubscribedEvents();

		$this->assertArrayHasKey('modules.load.success', $subscriptions);
		$this->assertContains(array('mountRoutes'), $subscriptions['modules.load.success']);
	}

	public function testMountingRoutes()
	{
		$services   = new FauxContainer;
		$listener   = new EventListener;
		$collection = $this->getMock(
			'Message\\Cog\\Routing\\RouteCollection',
			array('getRouteCollection'),
			array(),
			'',
			false
		);

		$services['event.dispatcher'] = $services->share(function() {
			return new FauxDispatcher;
		});

		$generator = $this->getMock('Message\\Cog\\Routing\\UrlMatcher', array(), array(), '', false);

		$services['routing.matcher'] = $services->share(function() use ($generator) {
			return $generator;
		});

		$this->_modulePaths['UniformWares\\CustomModuleName'] = __DIR__.'/fixtures/module/example';

		$fnsUtility = $this->getMockBuilder('Message\\Cog\\Functions\\Utility')
			->disableOriginalConstructor()
			->getMock();

		// Set the default/traced vendor and module
		$fnsUtility
			->expects($this->any())
			->method('traceCallingModuleName')
			->will($this->returnValue('Message\\Cog'));

		$this->_referenceParser = new ReferenceParser(
			new Module\FauxLocator($this->_modulePaths),
			$fnsUtility
		);

		$this->assertInstanceOf('Message\\Cog\\Service\\ContainerAwareInterface', $listener);

		$listener->setContainer($services);

		// Set up expectations
		$routes = $this->getMock(
			'\\Message\\Cog\\Routing\\CollectionManager',
			array('compileRoutes'),
			array($this->_referenceParser)
		);

		$routes
			->expects($this->exactly(1))
			->method('compileRoutes')
			->will($this->returnValue($collection));

		$collection
			->expects($this->exactly(1))
			->method('getRouteCollection')
			->will($this->returnValue('my-route-collection'));

		$services['routes'] = $services->share(function() use ($routes) {
			return $routes;
		});

		$listener->mountRoutes();

		$this->assertSame('my-route-collection', $services['routes.compiled']);
		$this->assertTrue($services->isShared('routes.compiled'));

		$this->assertTrue($services['event.dispatcher']->isSubscriberRegistered('Symfony\Component\HttpKernel\EventListener\RouterListener'));
	}


}