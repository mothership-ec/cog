<?php

namespace Message\Cog\Test\Hash;

use Message\Cog\Hash\Hash;

class HashTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateReturnsValidAlgorithm()
	{
		$this->markTestSkipped('Awaiting refactor of Hash component');
		$algorithm = Services::get('config')->security->passwordAlgorithm;
		$hash = Hash::create($algorithm);

		$this->assertInstanceOf('\Mothership\Framework\Hash', $hash);
		$this->assertContains($algorithm, get_class($hash));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCreateThrowsInvalidAlgorithmException()
	{
		Hash::create('notvalid');
	}

	public function testSaltRandomness()
	{
		$this->markTestSkipped('Awaiting refactor of Hash component');
		$algorithm = Services::get('config')->security->passwordAlgorithm;
		$hash = Hash::create($algorithm);

		$previous = array();

		for($i = 0; $i < 10; $i++) {
			$salt = $hash->generateSalt(30);
			$this->assertNotContains($salt, $previous);
			$this->assertTrue(strlen($salt) == 30);
			$previous[] = $salt;
		}
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testInvalidDevRandomPath()
	{
		$this->markTestSkipped('Awaiting refactor of Hash component');
		$algorithm = Services::get('config')->security->passwordAlgorithm;
		$hash = Hash::create($algorithm);
		$salt = $hash->generateSalt(30, '/dev/path/doesnt/exist');
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testNoDevRandomResponse()
	{
		$this->markTestSkipped('Awaiting refactor of Hash component');
		$algorithm = Services::get('config')->security->passwordAlgorithm;
		$hash = Hash::create($algorithm);
		$salt = $hash->generateSalt(30, '/dev/null');
	}
}