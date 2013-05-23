<?php 

namespace Message\Cog\Test\Security\Hash;

use Message\Cog\Security\Hash\OSCommerce;

class OSCommerceTest extends \PHPUnit_Framework_TestCase
{
	protected $_saltGenerator;
	protected $_hash;

	public function setUp()
	{
		$this->_saltGenerator = $this->getMock('Message\Cog\Security\Salt');
		$this->_hash		  = new OSCommerce($this->_saltGenerator);
	}

	public function testEncryptTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = 'c85e0631a75447bee5fe420b2dcd6cbe:ThisIsASaltThisIsASalt';
		$this->assertEquals($hashed, $correctHash);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testEncryptFalse($string)
	{

		$hashed = $this->_hash->encrypt($string, 'ThisIsASaltThisIsASalt');

		$correctHash = 'c85e0631a75447bee5fe420b2dcd6cbe:ThisIsASaltThisIsASalt';
		$this->assertNotEquals($hashed, $correctHash);
	}

	public function testCheckTrue()
	{
		$hashed = $this->_hash->encrypt('aTestString', 'ThisIsASaltThisIsASalt');

		$correctHash = 'c85e0631a75447bee5fe420b2dcd6cbe:ThisIsASaltThisIsASalt';
		$this->assertEquals($hashed, $correctHash);
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


	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testSaltGeneratorHasSalt($string)
	{
		$salt = 'ThisIsASaltThisIsASalt';

		$hashed = $this->_hash->encrypt($string, $salt);

		$output_array = explode(OSCommerce::SALT_SEPARATOR, $hashed);

		$this->assertEquals($salt, $output_array[1]);
	}

	/**
	 * @dataProvider Message\Cog\Test\Security\Hash\DataProvider::getStrings
	 */
	public function testSaltGeneratorHasNoSalt($string)
	{
		$hashed = $this->_hash->encrypt($string);

		$output_array = explode(OSCommerce::SALT_SEPARATOR, $hashed);

		$salt = $output_array[1];

		$this->assertNotEquals($salt, '');
	}

}