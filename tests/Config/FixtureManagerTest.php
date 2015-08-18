<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\FixtureManager;
use Message\Cog\Config\Exception;

use Composer\Package\BasePackage;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

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
			__DIR__ . '/message/cog-cms/Message/Cog/resources/fixtures/config/',
			FixtureManager::getConfigFixtureDir($composer, $package)
		);
	}

	public function testGetFixtures()
	{
		$this->assertFalse(FixtureManager::getFixtures('/this/does/not/exist'));

		vfsStream::setup('fixtures');
		vfsStream::newDirectory('config')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newFile('myconfig.txt')
			->at(vfsStreamWrapper::getRoot()->getChild('config'));
		vfsStream::newFile('Not Yaml.php')
			->at(vfsStreamWrapper::getRoot()->getChild('config'));

		$this->assertFalse(FixtureManager::getFixtures(vfsStream::url('fixtures/config')));

		vfsStream::newFile('a-real-config.yml')
			->at(vfsStreamWrapper::getRoot()->getChild('config'));
		vfsStream::newFile('wishlist.yml')
			->at(vfsStreamWrapper::getRoot()->getChild('config'));

		$expected = array(
			'a-real-config.yml',
			'wishlist.yml',
		);

		$this->assertEquals($expected, FixtureManager::getFixtures(vfsStream::url('fixtures/config')));
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Configuration fixture directory `vfs://fixtures/config` is not readable
	 */
	public function testGetFixturesThrowsExceptionWhenDirectoryUnreadable()
	{
		vfsStream::setup('fixtures');
		vfsStream::newDirectory('config', 0333)
			->at(vfsStreamWrapper::getRoot());

		FixtureManager::getFixtures(vfsStream::url('fixtures/config'));
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Configuration fixture `myconfig.yml` is not readable
	 */
	public function testGetFixturesThrowsExceptionWhenFixtureUnreadable()
	{
		vfsStream::setup('fixtures');
		vfsStream::newDirectory('config')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newFile('myconfig.yml', 0000)
			->at(vfsStreamWrapper::getRoot()->getChild('config'));

		FixtureManager::getFixtures(vfsStream::url('fixtures/config'));
	}
}