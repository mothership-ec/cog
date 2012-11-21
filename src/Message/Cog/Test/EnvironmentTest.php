<?php

namespace Message\Cog\Test;

require __DIR__.'/../Environment.php';


use Message\Cog\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testNoAreas()
	{
		$env = new Environment(array());
	}

	public function testDetectingEnvironmentName()
	{
		putenv('TEST_ENV=staging');

		$env = new Environment(array('test'), 'TEST_ENV');

		$this->assertEquals('staging', $env->get());
		$this->assertNotEquals('bob', $env->get());

		putenv('TEST_ENV');
	}

	public function testDetectingEnvironmentNameUsingServerVars()
	{
		$_SERVER['TEST_ENV'] = 'dev';

		$env = new Environment(array('test'), 'TEST_ENV');
		$this->assertEquals('dev', $env->get());

		unset($_SERVER['TEST_ENV']);
	}

	public function testDetectingAreaName()
	{
		putenv('TEST_AREA=checkout');

		$env = new Environment(array('checkout'), null, 'TEST_AREA');

		$this->assertEquals('checkout', $env->area());
		$this->assertNotEquals('admin', $env->area());

		putenv('TEST_AREA');
	}

	public function testDetectingAreaNameUsingServerVars()
	{
		$_SERVER['TEST_AREA'] = 'admin';
		
		$env = new Environment(array('admin', 'blog', 'checkout'), null, 'TEST_AREA');
		$this->assertEquals('admin', $env->area());

		unset($_SERVER['TEST_AREA']);
	}

	public function testDetectingContext()
	{
		$env = new Environment(array('checkout'));

		// When running tests this will always be the console. Right?
		$this->assertEquals('console', $env->context());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSettingDisallowedEnvironmentName()
	{
		$env = new Environment(array('test'), 'TEST_ENV', 'TEST_AREA');
		$env->set('pingpong');
	}

	public function testSettingAllowedEnvironmentName()
	{
		$env = new Environment(array('test', 'bob'), 'TEST_ENV', 'TEST_AREA');
		$env->set('live');

		$this->assertEquals('live', $env->get());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSettingDisallowedAreaName()
	{
		$env = new Environment(array('test'), 'TEST_ENV', 'TEST_AREA');
		$env->setArea('pingpong');
	}

	public function testSettingAllowedAreaName()
	{
		$env = new Environment(array('test', 'blog', 'checkout'), 'TEST_ENV', 'TEST_AREA');
		$env->setArea('checkout');

		$this->assertEquals('checkout', $env->area());
	}

	public function testGettingAllowedAreas()
	{
		$areas = array(
			'checkout',
			'blog',
			'admin',
			'cms',
		);
		$env = new Environment($areas, 'TEST_ENV', 'TEST_AREA');
		
		$this->assertEquals($areas, $env->getAllowedAreas());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSettingDisallowedContextName()
	{
		$env = new Environment(array('test'), 'TEST_ENV', 'TEST_AREA');
		$env->setContext('shop');
	}

	public function testSettingAllowedContextName()
	{
		$env = new Environment(array('test'), 'TEST_ENV', 'TEST_AREA');
		$env->setContext('web');

		$this->assertEquals('web', $env->context());
	}

}