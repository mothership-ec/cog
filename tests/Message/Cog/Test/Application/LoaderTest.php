<?php

namespace MyTestApp\MyInitialisationModule {
	class AppLoader extends \Message\Cog\Application\Loader
	{
		public function _registerModules()
		{

		}
	}
}

namespace Message\Cog\Test\Application {
	use Message\Cog\Test\Application\FauxEnvironment;
	use Message\Cog\Test\Application\Context\FauxContext;
	use Message\Cog\Test\Event\FauxDispatcher;
	use Message\Cog\Test\Service\FauxContainer;
	use Message\Cog\Test\Module\FauxLoader as FauxModuleLoader;

	use Message\Cog\Service\Container as ServiceContainer;

	use Composer\Autoload\ClassLoader as ComposerAutoloader;

	use org\bovigo\vfs\vfsStream;
	use org\bovigo\vfs\vfsStreamWrapper;
	use org\bovigo\vfs\vfsStreamDirectory;
	use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

	class LoaderTest extends \PHPUnit_Framework_TestCase
	{
		const VFS_ROOT_DIR = 'root';

		protected $_moduleList = array(
			'FauxApp/MyModuleName',
			'Message/NotModuleName',
		);

		public function setUp()
		{
			// Set the command-line arguments (override the phpunit ones)
			$_SERVER['argv'] = array('/usr/bin/php', 'foo:bar1');
		}

		public function tearDown()
		{
			// The loader causes streams to be registered, we unregister them here
			$manager = new \Message\Cog\Filesystem\StreamWrapperManager;
			$manager->clear();
		}

		/**
		 * Get a mocked instance of `Application\Loader` with `_registerModules` set
		 * to return a list of faux module names.
		 *
		 * @param  string $baseDir       Base URL to pass to the constructor
		 * @param  array  $methodsToMock Array of methods to mock
		 * @return object                The mocked instance
		 */
		public function getLoader($baseDir, array $methodsToMock = array())
		{
			$loader = $this->getMockForAbstractClass(
				'Message\Cog\Application\Loader',
				array($baseDir),
				'',
				true,
				true,
				true,
				$methodsToMock
			);

			$loader
				->expects($this->any())
				->method('_registerModules')
				->will($this->returnValue($this->_moduleList));

			return $loader;
		}

		/**
		 * Use the `vfsStream` library to create the expected autoload file as
		 * `vendor/autoload.php`.
		 *
		 * @param integer $permission Octal permission level for the file
		 */
		public function createAutoloadFile($permission = 0755)
		{
			vfsStream::setup(self::VFS_ROOT_DIR);
			vfsStream::newDirectory('vendor')
				->at(vfsStreamWrapper::getRoot());
			vfsStream::newFile('autoload.php', $permission)
				->at(vfsStreamWrapper::getRoot()->getChild('vendor'));
		}

		/**
		 * Get the path to the directory that this file is located in.
		 *
		 * @return string The full path, with symlinks followed
		 */
		public function getWorkingBaseDir()
		{
			return realpath(__DIR__);
		}

		public function getValidContexts()
		{
			return array(
				array('web'),
				array('console'),
			);
		}

		public function testBaseDirTrailingSlashMadeConsistent()
		{
			$expectedBaseDir = '/this/is/a/folder/';

			$loader = $this->getLoader('/this/is/a/folder');
			$this->assertEquals($expectedBaseDir, $loader->getBaseDir());

			$loader = $this->getLoader($expectedBaseDir);
			$this->assertEquals($expectedBaseDir, $loader->getBaseDir());
		}

		public function testGetAppName()
		{
			$loader = new \MyTestApp\MyInitialisationModule\AppLoader('/');

			$this->assertEquals('MyTestApp', $loader->getAppName());
		}

		/**
		 * @expectedException \Exception
		 */
		public function testGetContextTooEarlyThrowsException()
		{
			$loader = $this->getLoader('/path/to/installation');
			$loader->getContext();
		}

		/**
		 * @dataProvider getValidContexts
		 */
		public function testSetContextWorks($contextName)
		{
			$this->createAutoloadFile();

			$loader      = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container   = new FauxContainer;
			$serviceName = 'app.context.' . $contextName;

			// Add the environment service
			$container['environment'] = $container->share(function($c) {
				return new FauxEnvironment;
			});

			// Add the context to the service container as expected by `setContext()`
			$container[$serviceName] = $container->share(function($c) {
				return new FauxContext;
			});

			// Force the context to change
			$container['environment']->setContext($contextName);

			$loader->setServiceContainer($container)->setContext();

			$this->assertEquals($container[$serviceName], $loader->getContext());
		}

		/**
		 * @expectedException        \RuntimeException
		 * @expectedExceptionMessage not defined on service container
		 */
		public function testContextClassNotFoundException()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container = new FauxContainer;

			// Add the environment service
			$container['environment'] = $container->share(function($c) {
				return new FauxEnvironment;
			});

			// Force the context to change
			$container['environment']->setContext('web');

			$loader->setServiceContainer($container)->setContext();
		}

		/**
		 * @expectedException        \LogicException
		 * @expectedExceptionMessage does not implement ContextInterface
		 */
		public function testContextClassDoesNotImplementInterfaceException()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container = new FauxContainer;

			// Add the environment service
			$container['environment'] = $container->share(function($c) {
				return new FauxEnvironment;
			});

			// Add the invalid context class
			$container['app.context.web'] = $container->share(function() {
				return new \stdClass;
			});

			// Force the context to change
			$container['environment']->setContext('web');

			$loader->setServiceContainer($container)->setContext();
		}

		public function testRunInvokesCorrectMethods()
		{
			$this->createAutoloadFile();

			$loader = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR), array(
				'initialise',
				'loadCog',
				'setContext',
				'loadModules',
				'execute',
			));

			$loader
				->expects($this->exactly(1))
				->method('initialise')
				->will($this->returnValue($loader));

			$loader
				->expects($this->exactly(1))
				->method('loadCog')
				->will($this->returnValue($loader));

			$loader
				->expects($this->exactly(1))
				->method('setContext')
				->will($this->returnValue($loader));

			$loader
				->expects($this->exactly(1))
				->method('loadModules')
				->will($this->returnValue($loader));

			$loader
				->expects($this->exactly(1))
				->method('execute');

			$loader->run();
		}

		public function testInitialiseSetsAutoloader()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container = new FauxContainer;

			$loader->setServiceContainer($container)->initialise();

			foreach (spl_autoload_functions() as $function) {
				if ($function instanceof ComposerAutoloader) {
					return true;
				}
				elseif (is_array($function)) {
					foreach ($function as $fn) {
						if ($fn instanceof ComposerAutoloader) {
							return true;
						}
					}
				}
			}

			$this->fail('Calling `initialise()` did not register the Composer SPL autoloader');
		}

		public function testInitialiseGetsDefaultServiceContainerInstance()
		{
			$this->createAutoloadFile();

			$container = ServiceContainer::instance();
			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));

			$loader->initialise();

			$this->assertInternalType('object', $container['class.loader']);
		}

		public function testInitialiseDefinesAutoloaderService()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container = new FauxContainer;

			$loader->setServiceContainer($container)->initialise();

			$this->assertTrue($container->isShared('class.loader'));

			$this->assertInstanceOf('Composer\Autoload\ClassLoader', $container['class.loader']);
		}

		/**
		 * @expectedException        RuntimeException
		 * @expectedExceptionMessage does not exist
		 */
		public function testAutoloaderNotFoundException()
		{
			$loader = $this->getLoader('/not/a/real/path');
			$loader->initialise();
		}

		/**
		 * @expectedException        RuntimeException
		 * @expectedExceptionMessage is not readable
		 */
		public function testAutoloaderNotReadableException()
		{
			$this->createAutoloadFile(0333); // 0333 = none readable, all writeable & executable
			$loader = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$loader->initialise();
		}

		public function testLoadCogDefinesBaseServices()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container = new FauxContainer;

			$loader->setServiceContainer($container)->initialise()->loadCog();

			$this->assertEquals($loader, $container['app.loader']);
			$this->assertTrue($container->isShared('app.loader'));

			$this->assertInstanceOf('Message\Cog\Bootstrap\LoaderInterface', $container['bootstrap.loader']);
		}

		public function testLoadModules()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR), array('loadCog'));
			$container = new FauxContainer;

			$loader->setServiceContainer($container)->initialise()->loadCog();

			$container['module.loader'] = $container->share(function() {
				return new FauxModuleLoader;
			});

			$loader->loadModules();

			foreach ($this->_moduleList as $moduleName) {
				$this->assertTrue($container['module.loader']->exists($moduleName));
			}
		}

		public function testExecution()
		{
			$this->createAutoloadFile();

			$loader      = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container   = new FauxContainer;
			$dispatcher  = new FauxDispatcher;

			$contextReturnVal = 'foobar';

			// Add the event dispatcher service
			$container['event.dispatcher'] = $container->share(function($c) use ($dispatcher) {
				return $dispatcher;
			});

			// Add the event dispatcher service
			$container['event'] = function($c) {
				return new \Message\Cog\Event\Event;
			};

			// Add the environment service
			$container['environment'] = $container->share(function($c) {
				return new FauxEnvironment;
			});

			// Force the context to change
			$container['environment']->setContext('web');

			$contextMock = $this->getMock('Message\Cog\Test\Application\Context\FauxContext');

			$contextMock
				->expects($this->exactly(1))
				->method('run')
				->will($this->returnValue($contextReturnVal));

			// Add the context to the service container as expected by `setContext()`
			$container['app.context.web'] = $container->share(function($c) use ($contextMock) {
				return $contextMock;
			});

			$returnVal = $loader->setServiceContainer($container)->setContext()->execute();

			$this->assertEquals($contextReturnVal, $returnVal);

			// Assert 'terminate' event fired
			$this->assertInstanceOf('Message\Cog\Event\Event', $dispatcher->getDispatchedEvent('terminate'));
		}

		public function testChainability()
		{
			$this->createAutoloadFile();

			$loader    = $this->getLoader(vfsStream::url(self::VFS_ROOT_DIR));
			$container = new FauxContainer;

			$this->assertEquals($loader, $loader->setServiceContainer($container));
			$this->assertEquals($loader, $loader->initialise());
			$this->assertEquals($loader, $loader->loadCog());

			$container['module.loader'] = $container->share(function() {
				return new FauxModuleLoader;
			});

			$this->assertEquals($loader, $loader->loadModules());
		}
	}
}