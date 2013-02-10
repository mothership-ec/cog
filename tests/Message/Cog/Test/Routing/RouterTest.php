<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\Routing\Router;
use Message\Cog\Routing\RequestContext;

class RouterTest extends \PHPUnit_Framework_TestCase
{
	const ROUTE_CONTROLLER_REFERENCE = 'Message:CMS:ClassName#viewMethod';

	protected $_referenceParser;

	public function setUp()
	{
		$this->_referenceParser = $this->getMockBuilder('Message\Cog\ReferenceParserInterface')
			->disableOriginalConstructor()
			->getMock();

		$this->_referenceParser
			->expects($this->any())
			->method('parse')
			->will($this->returnValue($this->_referenceParser));

		$this->_referenceParser
			->expects($this->any())
			->method('getSymfonyLogicalControllerName')
			->will($this->returnValue('Message\CMS\Controller\ClassName::viewMethod'));

		$this->_router = new Router($this->_referenceParser);
	}

	public function testBasicRouting()
	{
		$this->_router->add('user.view', '/view/user/{userID}', self::ROUTE_CONTROLLER_REFERENCE)
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
		$router  = new Router($this->_referenceParser, array(), $context);

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