<?php

namespace Message\Cog\Test\Security;

use Message\Cog\Security\Salt;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class SaltTest extends \PHPUnit_Framework_TestCase
{
	protected $_salt;
	protected $_badHash;
	protected $_dlength;

	public function setUp()
	{
		$this->_salt    = new Salt;
		$this->_dlength = Salt::DEFAULT_LENGTH;
	}

	public function testAllGenerateMethodsRespectLength()
	{
		$this->assertSame(10, strlen($this->_salt->generateFromUnixRandom(10)));
		$this->assertSame(10, strlen($this->_salt->generateFromOpenSSL(10)));
		$this->assertSame(10, strlen($this->_salt->generateNatively(10)));
		$this->assertSame(10, strlen($this->_salt->generate(10)));
	}

	public function testDefaultLengthUsed()
	{
		$this->assertSame($this->_dlength, strlen($this->_salt->generateFromUnixRandom($this->_dlength)));
		$this->assertSame($this->_dlength, strlen($this->_salt->generateFromOpenSSL($this->_dlength)));
		$this->assertSame($this->_dlength, strlen($this->_salt->generateNatively($this->_dlength)));
		$this->assertSame($this->_dlength, strlen($this->_salt->generate($this->_dlength)));
	}

	/**
	 * @expectedException        \UnexpectedValueException
	 * @expectedExceptionMessage could not be generated
	 */
	public function testGenerateThrowsExceptionWhenNoStringGenerated()
	{
		$this->markTestIncomplete('Needs writing. Will likely require mocking of `Salt`');
		// mock the 3 generating methods so they all return false, then run ->generate()
	}

	public function testGenerateReturnValuesFormat()
	{
		// for each, check the results are strings and match the regex [./0-9A-Za-z]
		$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_salt->generate($this->_dlength));
		$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_salt->generateFromUnixRandom($this->_dlength));
		$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_salt->generateFromOpenSSL($this->_dlength));
		$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_salt->generateNatively($this->_dlength));
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage Unable to read
	 */
	public function testGenerateUnixRandomThrowsExceptionWhenRandomNotFound()
	{
		vfsStream::setup('root');
		vfsStream::newDirectory('dev')
			->at(vfsStreamWrapper::getRoot());

		$this->_salt->generateFromUnixRandom(10, vfsStream::url('root') . '/dev/urandom');
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage Unable to read
	 */
	public function testGenerateUnixRandomThrowsExceptionWhenRandomNotReadable()
	{
		vfsStream::setup('root');
		vfsStream::newDirectory('dev')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newFile('urandom', 0000)
			->at(vfsStreamWrapper::getRoot()->getChild('dev'));

		$this->_salt->generateFromUnixRandom(10, vfsStream::url('root') . '/dev/urandom');
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage returned an empty value
	 */
	public function testGenerateUnixRandomThrowsExceptionWhenRandomEmpty()
	{
		vfsStream::setup('root');
		vfsStream::newDirectory('dev')
			->at(vfsStreamWrapper::getRoot());
		vfsStream::newFile('urandom')
			->at(vfsStreamWrapper::getRoot()->getChild('dev'));

		$this->_salt->generateFromUnixRandom(10, vfsStream::url('root') . '/dev/urandom');
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage Function `openssl_random_pseudo_bytes` does not exist.
	 */
	public function testGenerateOpenSSLThrowsExceptionWhenFunctionDoesNotExist()
	{
		if (function_exists('openssl_random_pseudo_bytes')) {
			$this->markTestSkipped('Cannot run test: openssl_random_pseudo_bytes function exists.');
		}

		$this->_salt->generateFromOpenSSL();
	}

	public function getValidLengths()
	{
		return array(
			array(1),
			array(0),
			array(100),
			array(50),
			array(32),
			array(8),
		);
	}
}