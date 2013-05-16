<?php

namespace Message\Cog\Test\HTTP;

use Message\Cog\HTTP\StatusException;

class StatusExceptionTest extends \PHPUnit_Framework_TestCase
{
	public function testConstantCodes()
	{
		$this->assertEquals(303, StatusException::FORBIDDEN);

		$this->assertEquals(404, StatusException::NOT_FOUND);
		$this->assertEquals(405, StatusException::NOT_ALLOWED);
		$this->assertEquals(406, StatusException::NOT_ACCEPTABLE);

		$this->assertEquals(500, StatusException::SERVER_ERROR);
		$this->assertEquals(503, StatusException::SERVICE_UNAVAILABLE);
	}
}