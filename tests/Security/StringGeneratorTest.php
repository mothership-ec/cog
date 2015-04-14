<?php

namespace Message\Cog\Test\Security;

use Message\Cog\Security\StringGenerator;

class StringGeneratorTest extends \PHPUnit_Framework_TestCase
{
	private $_stringGenerator;

	public function setUp()
	{
		$this->_stringGenerator = new StringGenerator;		
	}

	public function testGenerateDefault()
	{
		for ($i = 0; $i < 1000; ++$i) {
			$string = $this->_stringGenerator->generate();
			$this->assertEquals(strlen($string), StringGenerator::DEFAULT_LENGTH);
		}
	}

	public function testGenerateLength()
	{
		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generate(10);
			$this->assertEquals(strlen($string), 10);
		}

		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generate(50);
			$this->assertEquals(strlen($string), 50);
		}

	}

	public function testGenerateUnixLength()
	{
		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generateFromUnixRandom(10);
			$this->assertEquals(strlen($string), 10);
		}

		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generateFromUnixRandom(50);
			$this->assertEquals(strlen($string), 50);
		}
	}

	public function testGenerateSSLLength()
	{
		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generateFromOpenSSL(10);
			$this->assertEquals(strlen($string), 10);
		}

		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generateFromOpenSSL(50);
			$this->assertEquals(strlen($string), 50);
		}
	}

	public function testGenerateNativeLength()
	{
		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generateNatively(10);
			$this->assertEquals(strlen($string), 10);
		}

		for ($i = 0; $i < 100; ++$i) {
			$string = $this->_stringGenerator->generateNatively(50);
			$this->assertEquals(strlen($string), 50);
		}
	}
}