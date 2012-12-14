<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Message\Cog\Test\Controller;

use Message\Cog\Controller\ControllerResolver;
use Message\Cog\HTTP\Request;

/**
 * Unit tests for the `ControllerResolver` class.
 *
 * This file has been taken from Symfony and modified to use our files and
 * follow the slight changes we made to `ControllerResolver`.
 *
 * This is because `ControllerResolver` was taken from Symfony standalone
 * (because we do not need the whole component).
 */
class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException		\InvalidArgumentException
	 * @expectedExceptionMessage Unable to look for the controller as the "_controller" parameter is missing
	 */
	public function testGetControllerControllerAttributeNotSetException()
	{
		$resolver = new ControllerResolver();
		$request  = Request::create('/');

		$this->assertFalse($resolver->getController($request), '->getController() returns false when the request has no _controller attribute');
	}

	/**
	 * @expectedException		\InvalidArgumentException
	 * @expectedExceptionMessage Method "Message\Cog\Test\Controller\ControllerResolverTest::fakeMethod" does not exist
	 */
	public function testGetControllerMethodNotExistsException()
	{
		$resolver = new ControllerResolver();
		$request  = Request::create('/');

		$request->attributes->set('_controller', 'Message\Cog\Test\Controller\ControllerResolverTest::fakeMethod');
		$resolver->getController($request);
	}

	public function testGetController()
	{
		$resolver = new ControllerResolver();
		$request  = Request::create('/');

		$request->attributes->set('_controller', 'Message\Cog\Test\Controller\ControllerResolverTest::testGetController');
		$controller = $resolver->getController($request);
		$this->assertInstanceOf('Message\Cog\Test\Controller\ControllerResolverTest', $controller[0], '->getController() returns a PHP callable');

		$request->attributes->set('_controller', $lambda = function () {});
		$controller = $resolver->getController($request);
		$this->assertSame($lambda, $controller);

		$request->attributes->set('_controller', $this);
		$controller = $resolver->getController($request);
		$this->assertSame($this, $controller);

		$request->attributes->set('_controller', 'Message\Cog\Test\Controller\ControllerResolverTest');
		$controller = $resolver->getController($request);
		$this->assertInstanceOf('Message\Cog\Test\Controller\ControllerResolverTest', $controller);

		$request->attributes->set('_controller', array($this, 'controllerMethod1'));
		$controller = $resolver->getController($request);
		$this->assertSame(array($this, 'controllerMethod1'), $controller);

		$request->attributes->set('_controller', array('Message\Cog\Test\Controller\ControllerResolverTest', 'controllerMethod4'));
		$controller = $resolver->getController($request);
		$this->assertSame(array('Message\Cog\Test\Controller\ControllerResolverTest', 'controllerMethod4'), $controller);

		$request->attributes->set('_controller', 'Message\Cog\Test\Controller\some_controller_function');
		$controller = $resolver->getController($request);
		$this->assertSame('Message\Cog\Test\Controller\some_controller_function', $controller);

		$request->attributes->set('_controller', 'foo');
		try {
			$resolver->getController($request);
			$this->fail('->getController() throws an \InvalidArgumentException if the _controller attribute is not well-formatted');
		} catch (\Exception $e) {
			$this->assertInstanceOf('\InvalidArgumentException', $e, '->getController() throws an \InvalidArgumentException if the _controller attribute is not well-formatted');
		}

		$request->attributes->set('_controller', 'foo::bar');
		try {
			$resolver->getController($request);
			$this->fail('->getController() throws an \InvalidArgumentException if the _controller attribute contains a non-existent class');
		} catch (\Exception $e) {
			$this->assertInstanceOf('\InvalidArgumentException', $e, '->getController() throws an \InvalidArgumentException if the _controller attribute contains a non-existent class');
		}

		$request->attributes->set('_controller', 'Symfony\Component\HttpKernel\Tests\ControllerResolverTest::bar');
		try {
			$resolver->getController($request);
			$this->fail('->getController() throws an \InvalidArgumentException if the _controller attribute contains a non-existent method');
		} catch (\Exception $e) {
			$this->assertInstanceOf('\InvalidArgumentException', $e, '->getController() throws an \InvalidArgumentException if the _controller attribute contains a non-existent method');
		}
	}

	public function testGetArguments()
	{
		$resolver = new ControllerResolver();

		$request = Request::create('/');
		$controller = array(new self(), 'testGetArguments');
		$this->assertEquals(array(), $resolver->getArguments($request, $controller), '->getArguments() returns an empty array if the method takes no arguments');

		$request = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$controller = array(new self(), 'controllerMethod1');
		$this->assertEquals(array('foo'), $resolver->getArguments($request, $controller), '->getArguments() returns an array of arguments for the controller method');

		$request = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$controller = array(new self(), 'controllerMethod2');
		$this->assertEquals(array('foo', null), $resolver->getArguments($request, $controller), '->getArguments() uses default values if present');

		$request->attributes->set('bar', 'bar');
		$this->assertEquals(array('foo', 'bar'), $resolver->getArguments($request, $controller), '->getArguments() overrides default values if provided in the request attributes');

		$request = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$controller = function ($foo) {};
		$this->assertEquals(array('foo'), $resolver->getArguments($request, $controller));

		$request = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$controller = function ($foo, $bar = 'bar') {};
		$this->assertEquals(array('foo', 'bar'), $resolver->getArguments($request, $controller));

		$request = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$controller = new self();
		$this->assertEquals(array('foo', null), $resolver->getArguments($request, $controller));
		$request->attributes->set('bar', 'bar');
		$this->assertEquals(array('foo', 'bar'), $resolver->getArguments($request, $controller));

		$request = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$request->attributes->set('foobar', 'foobar');
		$controller = 'Message\Cog\Test\Controller\some_controller_function';
		$this->assertEquals(array('foo', 'foobar'), $resolver->getArguments($request, $controller));

		$request = Request::create('/');
		$controller = array(new self(), 'controllerMethod5');
		$this->assertEquals(array($request), $resolver->getArguments($request, $controller), '->getArguments() injects the request');
	}

	/**
     * @dataProvider getControllerCallablesForUndeterminedArgumentValues
     *
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage requires that you provide a value for the "$bar" argument
	 */
	public function testGetArgumentsCannotDetermineArgumentValueException($controller)
	{
        $resolver   = new ControllerResolver();
        $controller = $controller;
        $request    = Request::create('/');
		$request->attributes->set('foo', 'foo');
		$request->attributes->set('foobar', 'foobar');

		if (version_compare(PHP_VERSION, '5.3.16', '==')) {
			$this->markTestSkipped('PHP 5.3.16 has a major bug in the Reflection sub-system');
		}
		else {
			$resolver->getArguments($request, $controller);
		}
	}

    public function getControllerCallablesForUndeterminedArgumentValues()
    {
        return array(
            array(array(new self(), 'controllerMethod3')),
            array(new InvokableTestController()),
            array('Message\Cog\Test\Controller\some_controller_function2'),
        );
    }

	public function __invoke($foo, $bar = null)
	{
	}

	protected function controllerMethod1($foo)
	{
	}

	protected function controllerMethod2($foo, $bar = null)
	{
	}

	protected function controllerMethod3($foo, $bar = null, $foobar)
	{
	}

	protected static function controllerMethod4()
	{
	}

	protected function controllerMethod5(Request $request)
	{
	}
}

class InvokableTestController
{
    public function __invoke($foo, $bar = null, $foobar)
    {
    }
}

function some_controller_function($foo, $foobar)
{
}

function some_controller_function2($foo, $bar = null, $foobar)
{
}