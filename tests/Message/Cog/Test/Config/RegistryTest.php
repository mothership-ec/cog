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

	}

	public function testSettingConfigThatsAlreadySet()
	{

	}

	public function testSettingConfigThatsAlreadySetArrayAccess()
	{

	}

	public function testIteration()
	{
		// iterate over the class, test the order of groups is correct
	}
}