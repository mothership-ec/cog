<?php

namespace Message\Cog\Test\Application\Bootstrap;

use Message\Cog\Application\Bootstrap\Events as EventsBootstrap;
use Message\Cog\Debug\Profiler;
use Message\Cog\Application\Environment;
use Message\Cog\HTTP\CookieCollection;

use Message\Cog\Test\Application\FauxLoader;
use Message\Cog\Test\Event\FauxDispatcher;
use Message\Cog\Test\Service\FauxContainer;
use Message\Cog\Test\Routing\FauxRouter;

use org\bovigo\vfs\vfsStream;

class EventsTest extends \PHPUnit_Framework_TestCase
{
	protected $_dispatcher;
	protected $_bootstrap;

	public function setUp()
	{
		$this->_container  = new FauxContainer;
		$this->_dispatcher = new FauxDispatcher;
		$this->_bootstrap  = new EventsBootstrap;
		$fragmentHandler   = $this->getMock('Symfony\\Component\\HttpKernel\\Fragment\\FragmentHandler');
		$uriSigner         = $this->getMock('Symfony\Component\HttpKernel\UriSigner', array(), array('123'));

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

		$this->_container['http.cookies'] = $this->_container->share(function() {
			return new CookieCollection;
		});

		$this->_container['http.fragment_handler'] = $this->_container->share(function() use ($fragmentHandler) {
			return $fragmentHandler;
		});

		$this->_container['http.uri_signer'] = $this->_container->share(function() use ($uriSigner) {
			return $uriSigner;
		});

		$classLoaderMock = $this->getMock('Composer\\Autoload\\ClassLoader');
		$this->_container['app.loader'] = function($c) use ($classLoaderMock) {
			return new FauxLoader($classLoaderMock, vfsStream::url('root'));
		};

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
			'Symfony\Component\HttpKernel\EventListener\ResponseListener'
		));
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Symfony\Component\HttpKernel\EventListener\FragmentListener'
		));
	}

	public function testDebugSubscribersRegistered()
	{
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\Debug\EventListener'
		));
	}

	public function testFilesystemSubscribersRegistered()
	{
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\Filesystem\EventListener'
		));
	}

	public function testRoutingSubscribersRegistered()
	{
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\Routing\EventListener'
		));
	}

	public function testControllerSubscribersRegistered()
	{
		$this->assertTrue($this->_dispatcher->isSubscriberRegistered(
			'Message\Cog\Controller\EventListener'
		));
	}
}