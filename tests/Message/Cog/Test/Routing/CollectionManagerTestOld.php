<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\ReferenceParser;
use Message\Cog\Test\Module;

use Message\Cog\Routing\Router;
use Message\Cog\Routing\Route;
use Message\Cog\Routing\RouteCollection;
use Message\Cog\Routing\RequestContext;


class CollectionManagerTest extends \PHPUnit_Framework_TestCase
{
	const ROUTE_CONTROLLER_REFERENCE = 'Message:CMS:ClassName#viewMethod';

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

		$this->_collection = new RouteCollection($this->_referenceParser);

		$this->_router = new Router;
		$this->_router->setCollection($this->_collection);
	}

	public function testBasicRouting()
	{
		$this->_collection->add('user.view', '/view/user/{userID}', self::ROUTE_CONTROLLER_REFERENCE)
			->setRequirement('userID', '\d+');

		$match = $this->_router->match('/view/user/1234');

		$expected = array(
			'_controller' => 'Message\CMS\Controller\ClassName::viewMethod',
			'userID'      => '1234',
			'_route'      => 'user.view',
			'_format'     => 'html'
		);

		$this->assertEquals($expected, $match);
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
	 */
	public function testBasicRoutingWithRequirements()
	{
		$this->_router->add('user.view', '/view/user/{userID}', 'Message:Cog:ClassName#view')
			->setRequirement('userID', '\d+');

		$match = $this->_router->match('/view/user/bob');
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
	 */
	public function testMissingRoute()
	{
		$this->_router->add('blog.post', '/blog/post/{id}', 'Message:Cog:Blog##post');

		$match = $this->_router->match('/order/return/43');
	}

	public function testGeneratorWithParameters()
	{
		$this->_router->add('blog.post', '/blog/post/{id}', 'Message:Cog:ClassName#post');

		$url = $this->_router->generate('blog.post', array('id' => '123', 'name' => 'bob', 'type' => 'comment'));

		$this->assertEquals('/blog/post/123?name=bob&type=comment', $url);
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\MissingMandatoryParametersException
	 */
	public function testGeneratorWithMissingParameters()
	{
		$this->_router->add('order.return', '/order/{orderID}/return/{returnID}', 'Message:Cog:ClassName#return');

		$url = $this->_router->generate('order.return', array('returnID' => '123'));
	}

	public function testGeneratorWithDifferentScheme()
	{
		$this->_router->add('order.return', '/order/{orderID}/return/{returnID}', 'Message:Cog:ClassName#return')->setScheme('https');

		$url = $this->_router->generate('order.return', array('orderID' => '567', 'returnID' => '123'));

		$this->assertEquals('https://localhost/order/567/return/123', $url);
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\MethodNotAllowedException
	 */
	public function testMatcherWithDifferentMethod()
	{
		$context = new RequestContext('', 'POST');
		$router  = new Router(array(), $context);

		$router->add('order.view', '/order/view/{orderID}', 'Order#view')
			->setMethod('GET');

		$match = $router->match('/order/view/9342');
	}

	public function testMatcherCaching()
	{
		// todo
		//$this->_router->setCache();

		$router = clone $this->_router;
		$router->add('blog.post', '/blog/post/{id}', 'Message:Cog:Blog##post');
		$match = $router->match('/blog/post/9342');

		// This time routes should be loaded from cache

		$router = clone $this->_router;
		$router->add('blog.post', '/blog/post/{id}', 'Message:Cog:Blog##post');
		$match = $router->match('/blog/post/9342');


		$this->markTestIncomplete(
			'Needs integration with the cache class'
		);
	}
}