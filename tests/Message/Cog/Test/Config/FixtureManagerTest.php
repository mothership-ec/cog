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

		$manager::postInstall($event);
		$manager::preUpdate($event);
		$manager::postUpdate($event);
	}

	public function testPostInstallCopiesConfigFixtures()
	{
		$manager  = $this->getMock('Message\Cog\Config\FixtureManager', array('getFixtures', 'isPackageCogModule', 'getConfigFixtureDir', 'getWorkingDir'));
		$composer = $this->getComposerInstance();
		$event    = $this->getMock('Composer\Script\PackageEvent', array('getOperation', 'getComposer', 'getIO'));
		$package  = $this->getMock('Composer\Package\BasePackage', array('getPrettyName', 'getTargetDir'));
		$io       = $this->getMock('Composer\IO\IOInterface', array('write'));
		$fixtures = array(
			'alerts.yml',
			'rss.yml',
			'comments.yml',
		);

		$package
			->expects($this->any())
			->method('getPrettyName')
			->will($this->returnValue('message/cog-cms'));

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
			->will($this->returnValue($fixtures));

		$vfs = vfsStream::setup('root');
		vfsStream::newDirectory('application')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newDirectory('config')
			->at(vfsStreamWrapper::getRoot()->getChild('application'));
		vfsStream::newDirectory('fixtures')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newDirectory('config')
			->at(vfsStreamWrapper::getRoot()->getChild('fixtures'));

		foreach ($fixtures as $i => $fixture) {
			$io
				->expects($this->at($i))
				->method('write')
				->with('<info>Moved package `message/cog-cms` config fixture `' . $fixture . '` to application config directory.</info>');

			vfsStream::newFile($fixture)
				->at(vfsStreamWrapper::getRoot()->getChild('fixtures')->getChild('config'));
		}

		$manager
			::staticExpects($this->any())
			->method('getWorkingDir')
			->will($this->returnValue(vfsStream::url('root/application') . '/'));

		$manager
			::staticExpects($this->any())
			->method('getConfigFixtureDir')
			->will($this->returnValue(vfsStream::url('root/fixtures/config') . '/'));

		$manager::postInstall($event);

		$fixtureDir = $vfs->getChild('application')->getChild('config');
		foreach ($fixtures as $fixture) {
			$this->assertTrue($fixtureDir->hasChild($fixture));
		}
	}

	public function testUpdateConfigFixtureComparisons()
	{
		$manager  = $this->getMock('Message\Cog\Config\FixtureManager', array('getFixtures', 'isPackageCogModule', 'getConfigFixtureDir'));
		$composer = $this->getComposerInstance();
		$event    = $this->getMock('Composer\Script\PackageEvent', array('getOperation', 'getComposer', 'getIO'));
		$package  = $this->getMock('Composer\Package\BasePackage', array('getPrettyName', 'getTargetDir'));
		$io       = $this->getMock('Composer\IO\IOInterface', array('write'));
		$fixtures = array(
			'alerts.yml'   => "new: joe@message.co.uk\nedit: team@message.co.uk",
			'rss.yml'      => "enabled: true\npost-types: all",
			'comments.yml' => "enabled: true\napproval: true\nlimit: 50",
		);

		$package
			->expects($this->any())
			->method('getPrettyName')
			->will($this->returnValue('message/cog-cms'));

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
			->will($this->returnValue(array_keys($fixtures)));

		$vfs = vfsStream::setup('fixtures');
		vfsStream::newDirectory('config')
			->at(vfsStreamWrapper::getRoot());

		foreach ($fixtures as $fixture => $content) {
			vfsStream::newFile($fixture)
				->setContent($content)
				->at(vfsStreamWrapper::getRoot()->getChild('config'));
		}

		$manager
			::staticExpects($this->any())
			->method('getConfigFixtureDir')
			->will($this->returnValue(vfsStream::url('fixtures/config') . '/'));

		$manager::preUpdate($event);

		vfsStreamWrapper::getRoot()->getChild('config')->getChild('rss.yml')
			->setContent("enabled: false\npost-types: all");

		$io
			->expects($this->exactly(1))
			->method('write')
			->with('<warning>Package `message/cog-cms` config fixture `rss.yml` has changed: please review manually.</warning>');

		$manager::postUpdate($event);
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
			__DIR__ . '/message/cog-cms/Message/Cog/fixtures/config/',
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