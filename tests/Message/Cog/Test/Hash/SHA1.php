<?php

namespace Message\Cog\Test\Hash;

use Message\Cog\Hash\SHA1;

class SHA1Test extends \PHPUnit_Framework_TestCase
{
	protected $_hash;

	public function setUp()
	{
		$this->_hash = new SHA1;
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testPasswordEncrypting($string)
	{
		$hashed = $this->_hash->encrypt($string);
		$this->assertTrue($this->_hash->check($string, $hashed));
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testEncryptValidSHA1($string)
	{
		$hashed = $this->_hash->encrypt($string);
		$this->assertTrue(ctype_xdigit($hashed) && strlen($hashed) === 40);
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testHashedPasswordDifferent($string)
	{
		$hashed = $this->_hash->encrypt($string);
		$this->assertNotEquals($string, $hashed);
	}

	public function testFailedCheckReturnsFalse()
	{
		$this->assertFalse($this->_hash->check('a different one', 'not even a hash'));
	}

	public function getStrings()
	{
		return array(
			array('joe password 123'),
			array('a nice password'),
			array(''), // empty string, shock horror!
			array(' '),
			array('PASSWORDREALLYLONGPASSWORDREALLYLONGPASSWORDREALLYLONG AND HAS LOTS OF SPACES'),
		);
	}
}