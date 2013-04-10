<?php

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\SHA1;

class SHA1Test extends \PHPUnit_Framework_TestCase
{
	protected $_saltGenerator;
	protected $_hash;

	public function setUp()
	{
		$this->_saltGenerator = $this->getMock('Message\Cog\Security\Salt');
		$this->_hash 		  = new SHA1($this->_saltGenerator);
	}

	public function testEncryptTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = '6d8d7cc5a873f827bca59a36ad8b0b5e9b1f5698:ThisIsASaltThisIsASalt';
		$this->assertEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testEncryptFalse($string)
	{
		$hashed = $this->_hash->encrypt($string, 'ThisIsASaltThisIsASalt');

		$correctHash = '67e761c6b62b03293cc7c0851a2a015266ec47cd:';
		$this->assertNotEquals($hashed, $correctHash);
	}

	public function testCheckTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$this->assertTrue($this->_hash->check('aTestString', $hashed));
	}

	/**
	 * @dataProvider getStrings
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

	public function getStrings()
	{
		return array(
			array('asimplepassword'), 		// String
			array('aSimplePassword'), 		// String with cases
			array('a simple password 123'), // String with integer and spaces
			array('a simple password'), 	// String with spaces
			array(''), 						// Blank password
			array('12345678'), 				// Integers only
			array('!@£$%^&*()_+=-'), 		// Special characters
			array('short'), 				// Short password
			array('password!@£$%^&*()'),	// String with special characters
			array('password123!@£$'),		// String with integer and special characters
			array('password 123 !@£$'),  	// String with integer, special characters and spaces
			array('areallylongpassword1234')// Long password with string and integer
			);
	}
}