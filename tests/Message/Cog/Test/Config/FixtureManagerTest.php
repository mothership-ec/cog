<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\FixtureManager;
use Message\Cog\Config\Exception;

use Composer\Package\BasePackage;

//NOTE: none of my static mocks are actually taking effect (one of the tests passes anyway
//but thats unrelated). No idea why it's not returning the values I say or throwing exceptions when I tell it to

class FauxOperation
{
	protected $_package;

	public function __construct(BasePackage $package)
	{
		$this->_package = $package;
	}

	public function getPackage()
	{
		return $this->_package;
	}

	public function getInitialPackage()
	{
		return $this->_package;
	}
}

class FixtureManagerTest extends \PHPUnit_Framework_TestCase
{
	public function getComposerInstance()
	{
		$composer       = $this->getMock('Composer\Composer', array('getConfig'));
		$composerConfig = $this->getMock('Composer\Config', array('get'));
		$baseDir        = __DIR__; // must be a real directory as realpath() is used

		$composerConfig
			->expects($this->any())
			->method('get')
			->with('vendor-dir')
			->will($this->returnValue(__DIR__));

		$composer
			->expects($this->any())
			->method('getConfig')
			->will($this->returnValue($composerConfig));

		return $composer;
	}

	public function testNonCogModulePackagesSkipped()
	{
		$event   = $this->getMock('Composer\Script\PackageEvent', array('getOperation'));
		$package = $this->getMock('Composer\Package\BasePackage', array('getPrettyName'));

		$package
			->expects($this->any())
			->method('getPrettyName')
			->will($this->returnValue('message/not-a-cogule'));

		$event
			->expects($this->any())
			->method('getOperation')
			->will($this->returnValue(new FauxOperation($package)));

		$this->assertFalse(FixtureManager::postInstall($event));
		$this->assertFalse(FixtureManager::preUpdate($event));
		$this->assertFalse(FixtureManager::postUpdate($event));
	}

	public function testQuietlyExistsIfNoConfigFixtures()
	{
		$manager  = $this->getMock('Message\Cog\Config\FixtureManager', array('getFixtures', 'isPackageCogModule'));
		$composer = $this->getComposerInstance();
		$event    = $this->getMock('Composer\Script\PackageEvent', array('getOperation', 'getComposer'));
		$package  = $this->getMock('Composer\Package\BasePackage', array('getPrettyName', 'getTargetDir'));

		$package
			->expects($this->any())
			->method('getPrettyName')
			->will($this->returnValue('message/cog-modulename'));

		$package
			->expects($this->any())
			->method('getTargetDir')
			->will($this->returnValue('Message/ModuleName'));

		$event
			->expects($this->any())
			->method('getOperation')
			->will($this->returnValue(new FauxOperation($package)));

		$event
			->expects($this->any())
			->method('getComposer')
			->will($this->returnValue($composer));

		$manager
			::staticExpects($this->any())
			->method('isPackageCogModule')
			->will($this->returnValue(true));

		$manager
			::staticExpects($this->any())
			->method('getFixtures')
			->will($this->returnValue(false));

		$this->assertFalse($manager::postInstall($event));
		$this->assertFalse($manager::preUpdate($event));
		$this->assertFalse($manager::postUpdate($event));
	}

	public function testExceptionsCaughtAndWrittenAsErrors()
	{
		$manager  = $this->getMock('Message\Cog\Config\FixtureManager', array('getFixtures', 'isPackageCogModule'));
		$composer = $this->getComposerInstance();
		$event    = $this->getMock('Composer\Script\PackageEvent', array('getOperation', 'getComposer', 'getIO'));
		$package  = $this->getMock('Composer\Package\BasePackage', array('getPrettyName', 'getTargetDir'));
		$io       = $this->getMock('Composer\IO\IOInterface', array('write'));

		$package
			->expects($this->any())
			->method('getPrettyName')
			->will($this->returnValue('message/cog-modulename'));

		$package
			->expects($this->any())
			->method('getTargetDir')
			->will($this->returnValue('Message/ModuleName'));

		$io
			->expects($this->exactly(3))
			->method('write')
			->will($this->returnValue('<error>test message</error>'));

		$event
			->expects($this->any())
			->method('getOperation')
			->will($this->returnValue(new FauxOperation($package)));

		$event
			->expects($this->any())
			->method('getComposer')
			->will($this->returnValue($composer));

		$event
			->expects($this->any())
			->method('getIO')
			->will($this->returnValue($io));

		$manager
			::staticExpects($this->any())
			->method('isPackageCogModule')
			->will($this->returnValue(true));

		$manager
			::staticExpects($this->any())
			->method('getFixtures')
			->will($this->throwException(new Exception('test message')));

		#$this->markTestIncomplete('for some infuriating reason, the above exception isn\'t getting thrown');

		$manager::postInstall($event);
		$manager::preUpdate($event);
		$manager::postUpdate($event);
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
		$this->assertEquals(getcwd() . '/', FixtureManager::getWorkingDir());
	}

	public function testGetConfigFixtureDir()
	{
		$composer = $this->getComposerInstance();
		$package  = $this->getMock('Composer\Package\BasePackage', array('getPrettyName', 'getTargetDir'));

		$package
			->expects($this->any())
			->method('getPrettyName')
			->will($this->returnValue('message/cog-cms'));

		$package
			->expects($this->any())
			->method('getTargetDir')
			->will($this->returnValue('Message/Cog'));

		$this->assertEquals(
			__DIR__ . '/message/cog-cms/Message/Cog/Fixtures/Config/',
			FixtureManager::getConfigFixtureDir($composer, $package)
		);
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