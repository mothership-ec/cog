<?php

namespace Message\Cog\Test\Hash;

use Message\Cog\Hash\Bcrypt;

class BcryptTest extends \PHPUnit_Framework_TestCase
{
	protected $_hash;

	public function setUp()
	{
		$this->_hash = new Bcrypt;
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testPasswordEncrypting($string)
	{
		$salt   = $this->_hash->generateSalt();
		$hashed = $this->_hash->encrypt($string, $salt);

		$this->assertTrue($this->_hash->check($string, $hashed));
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testHashedPasswordDifferent($string)
	{
		$salt   = $this->_hash->generateSalt();
		$hashed = $this->_hash->encrypt($string, $salt);

		$this->assertNotEquals($string, $hashed);
	}

	public function testFailedCheckReturnsFalse()
	{
		$this->assertFalse($this->_hash->check('a different one', 'not even a hash'));
	}

	/**
	 * @dataProvider getStrings
	 * @expectedException \InvalidArgumentException
	 */
	public function testShortSalt($string)
	{
		$salt   = $this->_hash->generateSalt(20);
		$hashed = $this->_hash->encrypt($string, $salt);
	}

	public function getStrings()
	{
		return array(
			array('joe password 123'),
			array('a nice password'),
			array(''), // empty string, shock horror!
			array(' '),
			array('PASSWORDREALLYLONG AND HAS LOTS OF SPACES'),
			array('123456789'), // integers
			array('"|\/{}][]!@£$%^&*()_+=-'), // ascii characters
		);
	}
}