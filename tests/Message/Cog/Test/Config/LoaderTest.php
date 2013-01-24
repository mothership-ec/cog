<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\Loader;

use Message\Cog\Test\Application\FauxEnvironment;
use Message\Cog\Test\Service\FauxContainer;

// TODO: somewhere explicitly test that the slash is added to the dir

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config directory `/i/do/not/exist/` does not exist
	 */
	public function testExceptionThrownOnNonExistantDirectory()
	{
		$loader = new Loader('/i/do/not/exist/', new FauxContainer, new FauxEnvironment);
	}

	public function testLoading()
	{
		$env = new FauxEnvironment;
		$env->set('live');
		$env->setInstallation('server6');

		$loader   = new Loader(realpath(__DIR__) . '/fixtures', new FauxContainer, $env);
		$registry = new NonLoadingRegistry($loader);

		$loader->load($registry);

		// AHH CHICKEN AND EGG
		// Do some asserting!

		return $registry;
	}

	/**
	 * @depends testLoading
	 *
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config group `nonyaml` does not exist
	 */
	public function testNonYamlFilesIgnored(NonLoadingRegistry $registry)
	{
		$registry->nonyaml;
	}

	public function testExceptionThrownWhenDirectoryNotReadable()
	{

	}

	public function testExceptionThrownWhenConfigFileNotReadable()
	{

	}
}