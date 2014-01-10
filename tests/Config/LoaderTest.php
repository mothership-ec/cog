<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\Loader;

use Message\Cog\Test\Application\FauxEnvironment;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config directory `/i/do/not/exist/` does not exist
	 */
	public function testExceptionThrownOnNonExistantDirectory()
	{
		$loader = new Loader('/i/do/not/exist/', new FauxEnvironment);
	}

	public function testLoading()
	{
		$env = new FauxEnvironment;
		$env->set('live');
		$env->setInstallation('server6');

		$loader   = new Loader(realpath(__DIR__) . '/fixtures', $env);
		$registry = new NonLoadingRegistry($loader);
		$expected = include 'expected_groups.php';

		$loader->load($registry);

		$this->assertEquals($expected['example'], $registry->example);
		$this->assertEquals($expected['example1'], $registry->example1);
		$this->assertEquals($expected['example2'], $registry->example2);
		$this->assertEquals($expected['example3'], $registry->example3);

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

	/**
	 * This test doesn't assert anything as it simply checks that the
	 * non-presence of override directories is handled gracefully.
	 */
	public function testNonExistantOverrideDirectoriesGetIgnored()
	{
		vfsStream::setup('config');
		vfsStream::newDirectory('dev')
			->at(vfsStreamWrapper::getRoot());

		$env = new FauxEnvironment;
		$env->set('dev');
		$env->setInstallation('dev6');

		$loader = new Loader(vfsStream::url('config'), $env);
		$registry = new NonLoadingRegistry($loader);

		$loader->load($registry);
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config directory `vfs://config/dev/` is not readable
	 */
	public function testExceptionThrownWhenDirectoryNotReadable()
	{
		vfsStream::setup('config');
		vfsStream::newDirectory('dev', 0000)
			->at(vfsStreamWrapper::getRoot());

		$env = new FauxEnvironment;
		$env->set('dev');

		$loader = new Loader(vfsStream::url('config'), $env);
		$registry = new NonLoadingRegistry($loader);

		$loader->load($registry);
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config file `vfs://config/myconfig.yml` is not readable
	 */
	public function testExceptionThrownWhenConfigFileNotReadable()
	{
		vfsStream::setup('config');
		vfsStream::newFile('myconfig.yml', 0333)
			->at(vfsStreamWrapper::getRoot());

		$env = new FauxEnvironment;
		$env->set('staging');

		$loader = new Loader(vfsStream::url('config'), $env);
		$registry = new NonLoadingRegistry($loader);

		$loader->load($registry);
	}
}