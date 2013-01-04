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
		throw new \InvalidArgumentException('must be at least 22 bytes');
	}

	public function testEncryptSaltGeneratedWhenNonePassed()
	{
		$this->_saltGenerator
			->expects($this->exactly(1))
			->method('generate')
			->with(22)
			->will($this->returnValue('1234567890abcdefGHIJKL'));

		$this->_hash->encrypt('hello');
	}

	public function testPasswordEncrypting()
	{

	}

	public function testHashedPasswordDifferent()
	{

	}

	public function testFailedCheckReturnsFalse()
	{

	}
}