<?php

namespace Message\Cog\Test;

use Message\Cog\Application\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
	public function testDetectingDefaultEnvironmentName()
	{
		putenv('COG_ENV=staging');

		$env = new Environment;

		$this->assertEquals('staging', $env->get());
		$this->assertNotEquals('bob', $env->get());

		putenv('COG_ENV');
	}

	public function testDetectingEnvironmentName()
	{
		putenv('TEST_ENV=staging');

		$env = new Environment('TEST_ENV');

		$this->assertEquals('staging', $env->get());
		$this->assertNotEquals('bob', $env->get());

		putenv('TEST_ENV');
	}

	public function testDetectingEnvironmentNameUsingServerVars()
	{
		$_SERVER['TEST_ENV'] = 'dev';

		$env = new Environment('TEST_ENV');
		$this->assertEquals('dev', $env->get());

		unset($_SERVER['TEST_ENV']);
	}

	public function testDetectingContext()
	{
		$env = new Environment;

		// When running tests this will always be the console. Right?
		$this->assertEquals('console', $env->context());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSettingDisallowedEnvironmentName()
	{
		$env = new Environment;
		$env->set('pingpong');
	}

	public function testSettingAllowedEnvironmentName()
	{
		$env = new Environment;
		$env->set('live');

		$this->assertEquals('live', $env->get());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSettingDisallowedContextName()
	{
		$env = new Environment;
		$env->setContext('shop');
	}

	public function testSettingAllowedContextName()
	{
		$env = new Environment;
		$env->setContext('web');

		$this->assertEquals('web', $env->context());
	}

	public function testIsLocalShortcut()
	{
		$env = new Environment;
		$env->set('local');

		$this->assertTrue($env->isLocal());
	}

	public function testGettingEnvVar()
	{
		$env = new Environment;

		putenv('SPECIAL_PERSON=andy');
		$this->assertEquals('andy', $env->getEnvironmentVar('SPECIAL_PERSON'));
		putenv('SPECIAL_PERSON');

		$_SERVER['COOL_PERSON'] = 'rob';
		$this->assertEquals('rob', $env->getEnvironmentVar('COOL_PERSON'));
		unset($_SERVER['COOL_PERSON']);
	}
}