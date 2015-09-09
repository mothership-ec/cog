<?php

namespace Message\Cog\Test\Routing;

use Message\Cog\Routing\Route;
use Message\Cog\Routing\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

class RedirectableUrlMatcherTest extends \PHPUnit_Framework_TestCase
{
	public function testSchemeRedirect()
	{
		$coll = new RouteCollection();
		$coll->add('foo', new Route('/foo', array(), array('_scheme' => 'https')));

		$matcher = new UrlMatcher($coll, new RequestContext());
		$result = $matcher->match('/foo');

		$this->assertArrayHasKey('url', $result);
		$this->assertSame($result['url'], 'https://localhost/foo');
	}

	public function testNonStandHttpRedirect()
	{
		$coll = new RouteCollection();
		$coll->add('foo', new Route('/foo', array(), array('_scheme' => 'http')));

		$context = new RequestContext;
		$context->setHttpPort(8000);
		$context->setScheme('https');

		$matcher = new UrlMatcher($coll, $context);
		$result = $matcher->match('/foo');

		$this->assertArrayHasKey('url', $result);
		$this->assertSame($result['url'], 'http://localhost:8000/foo');
	}

	public function testNonStandHttpsRedirect()
	{
		$coll = new RouteCollection();
		$coll->add('foo', new Route('/foo', array(), array('_scheme' => 'https')));

		$context = new RequestContext;
		$context->setHttpsPort(9000);
		$context->setScheme('http');

		$matcher = new UrlMatcher($coll, $context);
		$result = $matcher->match('/foo');

		$this->assertArrayHasKey('url', $result);
		$this->assertSame($result['url'], 'https://localhost:9000/foo');
	}

	/**
	 * @expectedException \Symfony\Component\Routing\Exception\MethodNotAllowedException
	 */
	public function testPostOnlyRequest($value='')
	{
		$coll = new RouteCollection();
		$coll->add('foo', new Route('/foo', array(), array('_method' => 'POST|HEAD')));

		$context = new RequestContext('/foo', 'GET');

		$matcher = new UrlMatcher($coll, $context);
		$result = $matcher->match('/foo');
	}
}