<?php

namespace Message\Cog\Test\Application\Bootstrap;

use Message\Cog\Application\Bootstrap\Events as EventsBootstrap;
use Message\Cog\Debug\Profiler;
use Message\Cog\Application\Environment;

use Message\Cog\Test\Event\FauxDispatcher;
use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Routing\FauxRouter;

class EventsTest extends \PHPUnit_Framework_TestCase
{
	protected $_dispatcher;
	protected $_bootstrap;

	public function setUp()
	{
		$this->_container  = new FauxContainer;
		$this->_dispatcher = new FauxDispatcher;
		$this->_bootstrap  = new EventsBootstrap;

		// Set up services used when registering these events
		$this->_container['router'] = $this->_container->share(function() {
			return new FauxRouter;
		});

		$this->_container['profiler'] = $this->_container->share(function() {
			return new Profiler;
		});

		$this->_container['environment'] = $this->_container->share(function() {
			return new Environment;
		});

		$this->_bootstrap->setContainer($this->_container);

		$this->_bootstrap->registerEvents($this->_dispatcher);
	}

	public function testHTTPSubscribersRegistered()
	{
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\HTTP\EventListener\Request'
		));
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\HTTP\EventListener\Response'
		));
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\HTTP\EventListener\Exception'
		));
	}

	public function testDebugSubscribersRegistered()
	{
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\Debug\EventListener'
		));
	}
}