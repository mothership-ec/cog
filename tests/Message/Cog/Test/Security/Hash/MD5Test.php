<?php 

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\MD5;


class MD5Test extends \PHPUnit_Framework_TestCase
{

	protected $_saltGenerator;
	protected $_hash;

	public function setUp()
	{
		$this->_saltGenerator = $this->getMock('Message\Cog\Security\Salt');
		$this->_hash 		  = new MD5($this->_saltGenerator);
	}

	public function testEncryptTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = '08073d3767ac0d725b01c620e432e4f4:';
		$this->assertEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider getStrings
	 */
	public function testEncryptfalse($string)
	{
		$hashed = $this->_hash->encrypt($string, 'ThisIsASaltThisIsASalt');

		$correctHash = '08073d3767ac0d725b01c620e432e4f4:';
		$this->assertNotEquals($hashed, $correctHash);
	}

	public function testCheckTrue()
	{
		
	}

	public function testCheckFalse()
	{

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