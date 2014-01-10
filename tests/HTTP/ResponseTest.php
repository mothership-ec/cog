<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	static public function getErrorHttpCodes()
	{
		return array(
			array(400),
			array(401),
			array(402),
			array(403),
			array(404),
			array(405),
			array(406),
			array(407),
			array(408),
			array(409),
			array(410),
			array(411),
			array(412),
			array(413),
			array(414),
			array(415),
			array(416),
			array(417),
			array(500),
			array(501),
			array(502),
			array(503),
			array(504),
			array(505),
		);
	}

	/**
	 * @dataProvider getErrorHttpCodes
	 */
	public function testIsError($httpCode)
	{
		$response = new Response('', $httpCode);

		$this->assertTrue($response->isError());
	}
}