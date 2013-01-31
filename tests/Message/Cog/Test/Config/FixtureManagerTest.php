<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\FixtureManager;

class FixtureManagerTest extends \PHPUnit_Framework_TestCase
{
	public function testNonCogModulePackagesSkipped()
	{
		// all 3
	}

	public function testNoConfigFixtureDirectoryQuietlyExits()
	{
// all 3
	}

	public function testEmptyConfigFixtureDirectoryQuietlyExists()
	{
// all 3
	}

	public function testExceptionsCaughtAndWrittenAsErrors()
	{
		// all 3
	}

	public function testPostInstallCopiesConfigFixtures()
	{

	}

	public function testPostInstallErrorOnCopyConfigFixtureFailure()
	{

	}

	public function testUpdateConfigFixtureComparisons()
	{

	}

	public function testGetWorkingDir()
	{
		// assert ends in trailing slash
		// anything else we can do? getcwd() comparison?
	}

	public function testGetConfigFixtureDir()
	{

	}

	public function testIsPackageCogModule()
	{
		$package = $this->getMock('Composer\Package\BasePackage', array('getPrettyName'));

		$package
			->expects($this->at(0))
			->method('getPrettyName')
			->will($this->returnValue('message/cog-wishlist'));

		$package
			->expects($this->at(1))
			->method('getPrettyName')
			->will($this->returnValue('somebodyelse/cog-custommodule'));

		$package
			->expects($this->at(2))
			->method('getPrettyName')
			->will($this->returnValue('message/nota-cog-module'));

		$this->assertTrue(FixtureManager::isPackageCogModule($package));
		$this->assertTrue(FixtureManager::isPackageCogModule($package));
		$this->assertFalse(FixtureManager::isPackageCogModule($package));
	}

	public function testGetFixtures()
	{
		// test correct fixture files are returned
		// test non-yaml files are not returned
	}

	public function testGetFixturesThrowsExceptionWhenDirectoryUnreadable()
	{

	}

	public function testGetFixturesThrowsExceptionWhenFixtureUnreadable()
	{

	}
}