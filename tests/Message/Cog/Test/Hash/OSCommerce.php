<?php

namespace Message\Cog\Test\Hash;

use Message\Cog\Hash\OSCommerce;

class OSCommerceTest extends \PHPUnit_Framework_TestCase
{
	protected $_hash;

	public function setUp()
	{
		$this->_hash = new OSCommerce;
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testStringEncrypting($string)
	{
		$hashed = $this->_hash->encrypt($string);
		$this->assertTrue($this->_hash->check($string, $hashed));
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testHashedPasswordDifferent($string)
	{
		$hashed = $this->_hash->encrypt($string);
		$this->assertNotEquals($string, $hashed);
	}

	/**
	 * @dataProvider getStringsWithSalts
	 */
	public function testEncryptAcceptsSalt($string, $salt)
	{
		$hashed = $this->_hash->encrypt($string, $salt);
		$this->assertTrue($this->_hash->check($string, $hashed));
	}

	public function testFailedCheckReturnsFalse()
	{
		$this->assertFalse($this->_hash->check('a different one', 'invalidhash:salt'));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckInvalidHashThrowsException()
	{
		$this->assertFalse($this->_hash->check('a different one', 'not even a hash'));
	}

	public function getStrings()
	{
		return array(
			array('joe password 123'),
			array('a nice password'),
			array(''), // empty password, shock horror!
			array(' '),
			array('PASSWORDREALLYLONG AND HAS LOTS OF SPACES'),
		);
	}

	public function getStringsWithSalts()
	{
		$return = array();
		$salts = array(
			12345,
			'abcdef',
			'5',
			'random salt',
		);
		foreach ($this->getStrings() as $key => $args) {
			// Get salt with the same key if it exists
			if (isset($salts[$key])) {
				$salt = $salts[$key];
			}
			// Otherwise, use a random salt
			else {
				$salt = $salts[array_rand($salts)];
			}
			array_push($args, $salt);
			$return[] = $args;
		}
		return $return;
	}

}