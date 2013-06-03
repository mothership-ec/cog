<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\ReferenceParser;
use Message\Cog\Test\Module;

use Message\Cog\Routing\Router;
use Message\Cog\Routing\Route;
use Message\Cog\Routing\RouteCollection;
use Message\Cog\Routing\RequestContext;


class RouterTest extends \PHPUnit_Framework_TestCase
{
	const ROUTE_CONTROLLER_REFERENCE = 'Message:CMS::ClassName#viewMethod';

	protected $_referenceParser;

	const DEFAULT_VENDOR = 'Message';
	const DEFAULT_MODULE = 'CMS';

	public function setUp()
	{
		$this->_modulePaths = array(
			'Message\\Cog'                   => '/path/to/installation/vendor/message/cog/src',
			'Message\\CMS'                   => '/path/to/installation/vendor/message/cog-cms',
			'Commerce\\Core'                 => '/path/to/installation/vendor/message/commerce',
			'Commerce\\Epos'                 => '/path/to/installation/vendor/message/commerce',
		);

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
		$this->_router->setRouteCollection($this->_collection->getRouteCollection());
	}

	public function testBasicRouting()
	{
		$this->_collection->add('user.view', '/view/user/{userID}', self::ROUTE_CONTROLLER_REFERENCE)
			->setRequirement('userID', '\d+');

		$match = $this->_router->match('/view/user/1234');

		$expected = array(
			'_controller' => 'Message\CMS\ClassName::viewMethod',
			'userID'      => '1234',
			'_route'      => 'user.view',
			'_format'     => 'html',
			'_access'     => 'external',
		);

		$this->assertEquals($expected, $match);
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
	 */
	public function testBasicRoutingWithRequirements()
	{
		$this->_collection->add('user.view', '/view/user/{userID}', 'Message:Cog::ClassName#view')
			->setRequirement('userID', '\d+');

		$match = $this->_router->match('/view/user/bob');
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
	 */
	public function testMissingRoute()
	{
		$this->_collection->add('blog.post', '/blog/post/{id}', 'Message:Cog::Blog##post');

		$match = $this->_router->match('/order/return/43');
	}

	public function testGeneratorWithParameters()
	{
		$this->_collection->add('blog.post', '/blog/post/{id}', 'Message:Cog::ClassName#post');

		$url = $this->_router->generate('blog.post', array('id' => '123', 'name' => 'bob', 'type' => 'comment'));

		$this->assertEquals('/blog/post/123?name=bob&type=comment', $url);
	}

	/**
	 * @expectedException Symfony\Component\Routing\Exception\MissingMandatoryParametersException
	 */
	public function testGeneratorWithMissingParameters()
	{
		$this->_collection->add('order.return', '/order/{orderID}/return/{returnID}', 'Message:Cog::ClassName#return');

		$url = $this->_router->generate('order.return', array('returnID' => '123'));
	}

	public function testGeneratorWithDifferentScheme()
	{
		$this->_collection->add('order.return', '/order/{orderID}/return/{returnID}', 'Message:Cog::ClassName#return')->setScheme('https');

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
		$router->setRouteCollection($this->_collection->getRouteCollection());

		$this->_collection->add('order.view', '/order/view/{orderID}', '::Order#view')
			->setMethod('GET');

		$match = $router->match('/order/view/9342');
	}

	public function testSettingContext()
	{
		$router  = new Router(array(), new RequestContext('', 'POST'));
		$context = new RequestContext('/orders', 'GET');
		$router->setRouteCollection($this->_collection->getRouteCollection());
		$router->setContext($context);

		$this->assertSame($context, $router->getContext());

	}

	public function testSettingOptions()
	{
		$this->_router->setOptions(array(
			'matcher_class'    		=> 'SomeClass',
			'matcher_cache_class'   => 'CacheClass',
		));

		$this->assertSame('SomeClass', $this->_router->getOption('matcher_class'));
		$this->assertSame('CacheClass', $this->_router->getOption('matcher_cache_class'));

		$this->_router->setOption('debug', true);

		$this->assertSame(true, $this->_router->getOption('debug'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSettingInvalidOptions()
	{
		$this->_router->setOptions(array(
			'bad_key' => 'SomeValue',
		));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testSettingInvalidOption()
	{
		$this->_router->setOption('bad_key', 'SomeValue');
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testGettingInvalidOption()
	{
		$this->_router->getOption('bad_key');
	}

	public function testOnlyOneMatcherIsCreated()
	{
		$matcher = $this->_router->getMatcher();

		$this->assertSame($matcher, $this->_router->getMatcher());
	}

	public function testOnlyOneGeneratorIsCreated()
	{
		$matcher = $this->_router->getGenerator();

		$this->assertSame($matcher, $this->_router->getGenerator());
	}
}