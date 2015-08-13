<?php

namespace Message\Cog\Test\Validation;

use Message\Cog\Validation\Loader;
use Message\Cog\Validation\Messages;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Loader
	 */
	protected $_loader;

	protected $_messages;
	/**
	 * @var DummyCollection
	 */
	protected $_collection;

	public function setUp()
	{
		$this->_messages = $this->getMock('\Message\Cog\Validation\Messages');
		$this->_collection = new DummyCollection;
		$this->_loader = new Loader($this->_messages, array());
	}

	public function testRegisterClasses()
	{
		$this->assertEquals($this->_loader, $this->_loader->registerClasses(array($this->_collection)));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testRegisterClassesInvalid()
	{
		$this->_loader->registerClasses(array(new \stdClass));

		$this->fail('Exception not thrown');
	}

	public function testRegisterRule()
	{
		$loader = $this->_loader->registerRule('testRule', array($this->_collection, 'testRule'), 'test');
		$this->assertInstanceOf('\Message\Cog\Validation\Loader', $loader);
	}

	public function testRegisterFilter()
	{
		$loader = $this->_loader->registerFilter('testFilter', array($this->_collection, 'testFilter'), 'test');
		$this->assertInstanceOf('\Message\Cog\Validation\Loader', $loader);
	}

	public function testGetRule()
	{
		$this->_loader
			->registerRule('testRule', array($this->_collection, 'testRule'), 'test');

		$rule = $this->_loader->getRule('testRule');

		$this->assertTrue(is_array($rule));
		$this->assertEquals(2, count($rule));
		$this->assertInstanceOf('\Message\Cog\Test\Validation\DummyCollection', $rule[0]);
		$this->assertEquals('testRule', $rule[1]);
	}

	public function testGetRuleFalse()
	{
		$this->assertFalse($this->_loader->getRule('Test'));
	}

	public function testGetFilter()
	{
		$this->_loader->registerFilter('testFilter', array($this->_collection, 'testFilter'));

		$filter = $this->_loader->getFilter('testFilter');

		$this->assertTrue(is_array($filter));
		$this->assertEquals(2, count($filter));
		$this->assertInstanceOf('\Message\Cog\Test\Validation\DummyCollection', $filter[0]);
		$this->assertEquals('testFilter', $filter[1]);
	}

	public function testGetFilterFalse()
	{
		$this->assertFalse($this->_loader->getFilter('Test'));
	}

	public function testGetRules()
	{
		$this->assertTrue(is_array($this->_loader->getRules()));
	}

	public function testGetFilters()
	{
		$this->assertTrue(is_array($this->_loader->getFilters()));
	}

	public function testSetGetMessages()
	{
		$messages = new Messages;
		$this->_loader->setMessages($messages);
		$this->assertSame($messages, $this->_loader->getMessages());
	}

	/**
	 * @expectedException \Exception
	 */
	public function testRegisterRuleFailDuplicate()
	{
		$this->_loader->registerRule('testRule', array($this->_collection, 'testRule'), 'test');
		$this->_loader->registerRule('testRule', array($this->_collection, 'testRule'), 'test');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testRegisterFilterFailDuplicate()
	{
		$this->_loader->registerFilter('testFilter', array($this->_collection, 'testFilter'));
		$this->_loader->registerFilter('testFilter', array($this->_collection, 'testFilter'));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testRegisterRuleFailNotCallable()
	{
		$this->_loader->registerRule('testRule', array($this->_collection, 'not a real method'), 'testRule');
	}

	/**
	 * @expectedException \Exception
	 */
	public function testRegisterFilterFailNotCallable()
	{
		$this->_loader->registerFilter('testFilter', array($this->_collection, 'not a real method'));
	}

}