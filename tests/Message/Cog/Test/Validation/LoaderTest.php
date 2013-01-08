<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_loader = new Loader(new Messages);
	}

	public function testNothing()
	{

	}
}