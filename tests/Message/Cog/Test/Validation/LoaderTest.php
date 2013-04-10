<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Messages;
use Message\Cog\Validation\Validator;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Loader
	 */
	protected $_loader;

	public function setUp()
	{
		$this->_loader = new Loader(new Validator, new Messages);
	}

	public function testNothing()
	{

	}

}