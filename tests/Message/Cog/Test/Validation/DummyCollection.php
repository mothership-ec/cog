<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\CollectionInterface;
use Message\Cog\Validation\Loader;

/**
*
*/
class DummyCollection implements CollectionInterface
{
	public function register(Loader $loader)
	{
		$loader->registerRule('testRule', array($this, 'testRule'), 'testRule')
			->registerFilter('testFilter', array($this, 'testFilter'));
	}

	public function testRule($var)
	{
		return true;
	}

	public function testFilter($var)
	{
		return true;
	}

}