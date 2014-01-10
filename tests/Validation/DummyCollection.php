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
			->registerRule('testFail', array($this, 'testFail'), 'testRule')
			->registerRule('isTest', array($this, 'isTest'), 'isTest')
			->registerFilter('testFilter', array($this, 'testFilter'));
	}

	public function testRule($var)
	{
		return true;
	}

	public function testFail($var)
	{
		return false;
	}

	public function isTest($var)
	{
		if ($var !== 'test') {
			return false;
		}

		return true;
	}

	public function testFilter($var)
	{
		return 'test';
	}

}