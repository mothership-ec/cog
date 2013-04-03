<?php

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\Bcrypt;

class BcryptTest extends \PHPUnit_Framework_TestCase
{
	protected $_saltGenerator;
	protected $_hash;

	public function setUp()
	{
		$this->_saltGenerator = $this->getMock('Message\Cog\Security\Salt');
		$this->_hash          = new Bcrypt($this->_saltGenerator);
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must be at least 22 bytes
	 */
	public function testEncryptWithShortSalt()
	{
		// throw new \InvalidArgumentException('must be at least 22 bytes');
		$this->_hash->encrypt('teststring', 'thisaint22');
	}

	public function testEncryptSaltGeneratedWhenNonePassed()
	{
		$this->_saltGenerator
			->expects($this->exactly(1))
			->method('generate')
			->with(22)
			->will($this->returnValue('1234567890abcdefGHIJKL'));

		$this->_hash->encrypt('test');
	}

	public function testPasswordEncrypting()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = '$2a$08$ThisIsASaltThisIsASaleOLLUepKCwdx3DsV55G8gHdWpoJN51Tq';
		$this->assertEquals($hashed, $correctHash);
	}

	public function testHashedPasswordDifferent()
	{
		$hashed = $this->_hash->encrypt('teststring', 'teststringteststringteststring');
		$this->assertNotEquals('teststring', $hashed);
	}

	public function testFailedCheckReturnsFalse()
	{
		$hashedString = '$2a$08$teststringteststringte3pPJGRyq.zU1T3w1gBA8hiqk1CuMAAu';
		$diff = $this->_hash->check('teststring', $hashedString);
 
		$this->assertTrue($diff);
	}

	/**
	* @expectedException 		\InvalidArgumentException
	* @expectedExceptionMessage contains invalid characters.
	*/
	public function testDisallowedCharactersInSalt()
	{
		$this->_hash->encrypt('testString', '**invalid_salt-Invalid_salt**');
	}
}