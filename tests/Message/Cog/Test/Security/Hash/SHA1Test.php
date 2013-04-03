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

		$correctHash = '67e761c6b62b03293cc7c0851a2a015266ec47cd:';
		$this->assertEquals($hashed, $correctHash);
	}
}