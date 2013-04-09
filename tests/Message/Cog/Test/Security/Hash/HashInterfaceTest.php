<?php 

class HashInterfaceTest extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->mock = $this->getMock('HashInterface');
	}

	public function testInterfaceInstance()
	{
		$this->assertTrue($this->mock instanceof HashInterface);
	}

}