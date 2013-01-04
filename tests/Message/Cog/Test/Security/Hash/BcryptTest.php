<?php

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\Bcrypt;

class BcryptTest extends \PHPUnit_Framework_TestCase
{
	protected $_hash;

	public function setUp()
	{
		$this->_hash = new Bcrypt;
	}

	/**
	 * @expectedException        InvalidArgumentException
	 * @expectedExceptionMessage must be at least 22 bytes
	 */
	public function testEncryptWithShortSalt()
	{

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