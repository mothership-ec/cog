<?php

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\SHA1;

class SHA1Test extends \PHPUnit_Framework_TestCase
{
	protected $_saltGenerator;
	protected $_hash;
	protected $_generatedSalt = 12345;

	public function setUp()
	{
		$this->_saltGenerator = $this->getMock('Message\Cog\Security\Salt');
		$this->_hash 		  = new SHA1($this->_saltGenerator);

		$this->_saltGenerator
			 ->expects($this->any())
			 ->method('generate')
			 ->will($this->returnValue($this->_generatedSalt));
	}

	public function testEncryptTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = '6d8d7cc5a873f827bca59a36ad8b0b5e9b1f5698:ThisIsASaltThisIsASalt';
		$this->assertEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testEncryptFalse($string)
	{
		$hashed = $this->_hash->encrypt($string, 'ThisIsASaltThisIsASalt');

		$correctHash = '67e761c6b62b03293cc7c0851a2a015266ec47cd:';
		$this->assertNotEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testSaltGeneratorHasSalt($string)
	{
		$salt = 'ThisIsASaltThisIsASalt';

		$hashed = $this->_hash->encrypt($string, $salt);

		list($hash, $hashSalt) = explode(SHA1::SALT_SEPARATOR, $hashed, 2);

		$this->assertEquals($salt, $hashSalt);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testSaltGeneratorHasNoSalt($string)
	{
		$hashed = $this->_hash->encrypt($string);

		list($hash, $hashSalt) = explode(SHA1::SALT_SEPARATOR, $hashed, 2);

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
		$this->assertFalse($this->_hash->check($string, 'invalidhash:invalid'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckInvalidHashThrowsException()
	{
		$this->assertFalse($this->_hash->check('test string', 'invalid hash'));
	}
}