<?php

namespace Message\Cog\Test\Filesystem;

class StreamWrapperShimTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \BadMethodCallException
	 */
	public function testBadMethodCall()
	{
		$wrapper = new FauxShim;	
		$wrapper->iDontExistAndNeverWill();
	}
}