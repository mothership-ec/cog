<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\Registry;
use Message\Cog\Config\Group;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
	protected $_configs;
	protected $_loader;
	protected $_registry;

	static public function getConfigFixtures()
	{
		static $return;

		if (!$return) {
			$test     = new Group;
			$db       = new Group;
			$merchant = new Group;

			$test->testVar   = 123;
			$test->stringVar = 'my special unit test';

			$db->host = 'localhost';
			$db->user = 'dbuser';
			$db->pass = '123password';
			$db->name = 'cog_db';

			$merchant->name     = 'Message';
			$merchant->currency = 'GBP';
			$merchant->products = array(
				(object) array(
					'id'    => 1,
					'name'  => 'Ping pong ball',
					'price' => 1.00
				),
				(object) array(
					'id'    => 2,
					'name'  => 'Ping pong bat',
					'price' => 12.00
				),
				(object) array(
					'id'    => 3,
					'name'  => 'Ping pong table',
					'price' => 229.99
				),
			);

			$return = array(
				'test'     => $test,
				'db'       => $db,
				'merchant' => $merchant,
			);
		}

		return $return;
	}

	public function setUp()
	{
		$this->_configs = self::getConfigFixtures();
		$this->_loader  = new FauxLoader;
		$this->_loader->addConfigs($this->_configs);

		$this->_registry = new Registry($this->_loader);
	}

	public function testGetting()
	{
		$configs = self::getConfigFixtures();

		$this->assertInstanceOf('Message\Cog\Config\Group', $this->_registry->test);
		$this->assertInstanceOf('Message\Cog\Config\Group', $this->_registry['test']);
		$this->assertInstanceOf('Message\Cog\Config\Group', $this->_registry->db);
		$this->assertInstanceOf('Message\Cog\Config\Group', $this->_registry['db']);
		$this->assertInstanceOf('Message\Cog\Config\Group', $this->_registry->merchant);
		$this->assertInstanceOf('Message\Cog\Config\Group', $this->_registry['merchant']);

		$this->assertEquals($configs['test'], $this->_registry->test);
		$this->assertEquals($configs['test'], $this->_registry['test']);
		$this->assertEquals($configs['db'], $this->_registry->db);
		$this->assertEquals($configs['db'], $this->_registry['db']);
		$this->assertEquals($configs['merchant'], $this->_registry->merchant);
		$this->assertEquals($configs['merchant'], $this->_registry['merchant']);

		$this->assertEquals($configs['merchant']->products, $this->_registry->merchant->products);
		$this->assertEquals($configs['merchant']->products, $this->_registry['merchant']->products);
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingNonExistantConfig()
	{
		$this->_registry->iDoNotExist;
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingNonExistantConfigArrayAccess()
	{
		$this->_registry['iDoNotExist'];
	}

	public function testSetting()
	{
		$registry    = new Registry(new FauxLoader);
		$firstConfig = array_shift($this->_configs);

		$registry->testingAdding = $firstConfig;

		$this->assertEquals(1, count($registry->getAll()));

		$registry['testingAddingArrayAccess'] = $firstConfig;

		$this->assertEquals(2, count($registry->getAll()));

		$this->assertEquals($firstConfig, $registry['testingAdding']);
		$this->assertEquals($firstConfig, $registry->testingAddingArrayAccess);
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage has already been set
	 */
	public function testSettingConfigThatsAlreadySet()
	{
		$this->_registry->test = new Group;
		$this->_registry->test = new Group;
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage has already been set
	 */
	public function testSettingConfigThatsAlreadySetArrayAccess()
	{
		$this->_registry['myAwesomeTest'] = new Group;
		$this->_registry['myAwesomeTest'] = new Group;
	}

	public function testIteration()
	{
		// Get something to trigger the lazy load
		$this->_registry->load();

		$expectedKeys = array_keys($this->_configs);

		foreach ($this->_registry as $id => $config) {
			$this->assertEquals(array_shift($expectedKeys), $id);
			$this->assertEquals(array_shift($this->_configs), $config);
		}
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config groups cannot be removed from the registry
	 */
	public function testUnsetting()
	{
		// Get something to trigger the lazy load, just incase
		$this->_registry->load();

		unset($this->_registry->test);
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage Config groups cannot be removed from the registry
	 */
	public function testUnsettingArrayAccess()
	{
		// Get something to trigger the lazy load, just incase
		$this->_registry->load();

		unset($this->_registry['test']);
	}

	public function testIsset()
	{
		// Trigger the lazy loading
		$this->_registry->load();

		$this->assertTrue(isset($this->_registry->test));
		$this->assertTrue(isset($this->_registry['test']));

		$this->assertTrue(isset($this->_registry->db));
		$this->assertTrue(isset($this->_registry['db']));

		$this->assertFalse(isset($this->_registry->iDoNotExist));
		$this->assertFalse(isset($this->_registry['iDoNotExist']));
	}

	public function testGetAll()
	{
		// Trigger lazy loading
		$this->_registry->load();

		$this->assertEquals($this->_configs, $this->_registry->getAll());
	}

	public function testLazyLoadingObjectAccess()
	{
		$this->setUpForLazyLoadingTests();

		$this->_registry->test;
		$this->_registry['test'];
		$this->_registry->getAll();
	}

	public function testLazyLoadingArrayAccess()
	{
		$this->setUpForLazyLoadingTests();

		$this->_registry['test'];
		$this->_registry->getAll();
		$this->_registry->test;
	}

	public function testLazyLoadingGetAll()
	{
		$this->setUpForLazyLoadingTests();

		$this->_registry->getAll();
		$this->_registry->test;
		$this->_registry['test'];
	}

	public function setUpForLazyLoadingTests()
	{
		$this->_loader = $this->getMock('Message\Cog\Test\Config\FauxLoader', array('load'));
		$this->_loader->addConfigs($this->_configs);

		$this->_registry = new Registry($this->_loader);

		$this->_loader
			->expects($this->exactly(1))
			->method('load')
			->with($this->_registry);

		foreach ($this->_configs as $id => $config) {
			$this->_registry->$id = $config;
		}
	}
}