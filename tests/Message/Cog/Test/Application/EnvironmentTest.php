<?php

namespace Message\Cog\Test\Application;

use Message\Cog\Application\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
	public function testDetectingDefaultEnvironmentName()
	{
		putenv('COG_ENV');

		$env = new Environment;

		$this->assertEquals('local', $env->get());
		$this->assertNotEquals('bob', $env->get());
	}

	public function testDetectingEnvironmentName()
	{
		putenv('TEST_ENV=staging');

		$env = new Environment('TEST_ENV');

		$this->assertEquals('staging', $env->get());
		$this->assertNotEquals('bob', $env->get());

		putenv('TEST_ENV=live-server6');

		$env = new Environment('TEST_ENV');

		$this->assertEquals('live', $env->get());
		$this->assertEquals('server6', $env->installation());
		$this->assertNotEquals('staging', $env->get());

		putenv('TEST_ENV');
	}

	public function testDetectingEnvironmentNameUsingServerVars()
	{
		$_SERVER['MY_ENV'] = 'dev';

		$env = new Environment('MY_ENV');
		$this->assertEquals('dev', $env->get());

		$_SERVER['MY_ENV'] = 'local-joe';

		$env = new Environment('MY_ENV');

		$this->assertEquals('local', $env->get());
		$this->assertEquals('joe', $env->installation());
		$this->assertNotEquals('dev', $env->get());

		unset($_SERVER['MY_ENV']);
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