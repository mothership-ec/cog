<?php

namespace Message\Cog\Test\Security;

use Message\Cog\Security\StringGenerator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class StringGeneratorTest extends \PHPUnit_Framework_TestCase
{
	protected $_salt;
	protected $_badHash;
	protected $_dlength;

	public function setUp()
	{
		$this->_salt    = new StringGenerator;
		$this->_dlength = StringGenerator::DEFAULT_LENGTH;
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

	public function testGenerateOpenSSLThrowsExceptionWhenFunctionDoesNotExist()
	{
		if (function_exists('openssl_random_pseudo_bytes')) {
			$this->assertTrue(true);
		} else {
			try {
				$this->_salt->generateFromOpenSSL();
			} catch (\RuntimeException $e) {
				$this->assertTrue(true);
			}

			$this->fail('RuntimeException not thrown');
		}
	}

	public function getValidLengths()
	{
		return array(
			[1],
			[0],
			[100],
			[50],
			[32],
			[8],
		);
	}
}