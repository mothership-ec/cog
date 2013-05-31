<?php

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\MD5;

class MD5Test extends \PHPUnit_Framework_TestCase
{

	protected $_saltGenerator;
	protected $_hash;
	protected $_generatedSalt = 12345;

	public function setUp()
	{
		$this->_saltGenerator = $this->getMock('Message\Cog\Security\Salt');
		$this->_hash 		  = new MD5($this->_saltGenerator);

		$this->_saltGenerator
			 ->expects($this->any())
			 ->method('generate')
			 ->will($this->returnValue($this->_generatedSalt));
	}

	public function testEncryptTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = '839fe6cd6eb560a2f9dbc19a2389c57d:ThisIsASaltThisIsASalt';
		$this->assertEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testEncryptfalse($string)
	{
		$hashed = $this->_hash->encrypt($string, 'ThisIsASaltThisIsASalt');

		$correctHash = '08073d3767ac0d725b01c620e432e4f4:';
		$this->assertNotEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testSaltGeneratorHasSalt($string)
	{
		$salt = 'ThisIsASaltThisIsASalt';

		$hashed = $this->_hash->encrypt($string, $salt);

		list($hash, $hashSalt) = explode(MD5::SALT_SEPARATOR, $hashed, 2);

		$this->assertEquals($salt, $hashSalt);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testSaltGeneratorHasNoSalt($string)
	{
		$hashed = $this->_hash->encrypt($string);

		list($hash, $hashSalt) = explode(MD5::SALT_SEPARATOR, $hashed, 2);

		$this->assertNotEmpty($hashSalt);
	}

	public function testCheckTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$this->assertTrue($this->_hash->check('aTestString', $hashed));
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testCheckFalse($string)
	{
		$this->assertFalse($this->_hash->check($string, 'invalidhash:invalidsalt'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckInvalidHashThrowsException()
	{
		$this->assertFalse($this->_hash->check('test string', 'invalid hash'));
	}
}