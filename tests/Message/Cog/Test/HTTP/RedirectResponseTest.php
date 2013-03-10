<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\RedirectResponse;
use Message\Cog\HTTP\Response;

class RedirectResponseTest extends \PHPUnit_Framework_TestCase
{
	static public function getNonRedirectHttpCodes()
	{
		$httpCodes = Response::$statusTexts;
		$return    = array();

		unset(
			$httpCodes[201],
			$httpCodes[301],
			$httpCodes[302],
			$httpCodes[303],
			$httpCodes[307],
			$httpCodes[308]
		);

		foreach ($httpCodes as $code => $text) {
			$return[] = array($code);
		}

		return $return;
	}

	/**
	 * @dataProvider getNonRedirectHttpCodes
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage is not a redirect
	 */
	public function testOnlyRedirectHttpCodesAllowed($httpCode)
	{
		$response = new RedirectResponse('http://www.google.co.uk.', $httpCode);
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage Cannot redirect to a blank URL
	 */
	public function testBlankRedirectUrlConstructor()
	{
		$response = new RedirectResponse('', 301);
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage Cannot redirect to a blank URL
	 */
	public function testBlankRedirectUrlSetter()
	{
		$response = new RedirectResponse('http://www.message.co.uk', 301);

		$response->setTargetUrl('');
	}

	public function testSetGetRedirectUrl()
	{
		$response  = new RedirectResponse('http://www.message.co.uk', 302);
		$newTarget = 'http://www.another-website.com/a-page/something.html';

		$response->setTargetUrl($newTarget);

		$this->assertEquals($newTarget, $response->getTargetUrl());
	}
}