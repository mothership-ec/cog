<?php

namespace Message\Cog\Test\Security;

use Message\Cog\Security\StringGenerator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class StringGeneratorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var StringGenerator
	 */
	protected $_stringGenerator;

	protected $_badHash;
	protected $_length;

	public function setUp()
	{
		$this->_stringGenerator = new StringGenerator;
		$this->_length          = StringGenerator::DEFAULT_LENGTH;
	}

	public function testAllGenerateMethodsRespectLength()
	{
		for ($i = 1; $i < 200; ++$i) {
			$this->assertSame(10, strlen($this->_stringGenerator->generateFromUnixRandom(10)));
			$this->assertSame(10, strlen($this->_stringGenerator->generateFromOpenSSL(10)));
			$this->assertSame(10, strlen($this->_stringGenerator->generateNatively(10)));
			$this->assertSame(10, strlen($this->_stringGenerator->generate(10)));
		}
	}

	public function testDefaultLengthUsed()
	{
		for ($i = 1; $i < 200; ++$i) {
			$this->assertSame($this->_length, strlen($this->_stringGenerator->generateFromUnixRandom($this->_length)));
			$this->assertSame($this->_length, strlen($this->_stringGenerator->generateFromOpenSSL($this->_length)));
			$this->assertSame($this->_length, strlen($this->_stringGenerator->generateNatively($this->_length)));
			$this->assertSame($this->_length, strlen($this->_stringGenerator->generate($this->_length)));
		}
	}

	public function testGenerateReturnValuesFormat()
	{
		for ($i = 1; $i < 200; ++$i) {
			// for each, check the results are strings and match the regex [./0-9A-Za-z]
			$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_stringGenerator->generate($this->_length));
			$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_stringGenerator->generateFromUnixRandom($this->_length));
			$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_stringGenerator->generateFromOpenSSL($this->_length));
			$this->assertRegExp("/[A-Za-z0-9\/\\.']/", $this->_stringGenerator->generateNatively($this->_length));
		}
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

		$this->_stringGenerator->generateFromUnixRandom(10, vfsStream::url('root') . '/dev/urandom');
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

		$this->_stringGenerator->generateFromUnixRandom(10, vfsStream::url('root') . '/dev/urandom');
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

		$this->_stringGenerator->generateFromUnixRandom(10, vfsStream::url('root') . '/dev/urandom');
	}

	public function testGenerateOpenSSLThrowsExceptionWhenFunctionDoesNotExist()
	{
		if (function_exists('openssl_random_pseudo_bytes')) {
			$this->assertTrue(true);
		} else {
			try {
				$this->_stringGenerator->generateFromOpenSSL();
			} catch (\RuntimeException $e) {
				$this->assertTrue(true);
			}

			$this->fail('RuntimeException not thrown');
		}
	}

	public function testGenerateNativelyNoLengthSet()
	{
		for ($i = 1; $i < 200; ++$i) {
			$string1 = $this->_stringGenerator->generateNatively();
			$string2 = $this->_stringGenerator->generateNatively();
			$this->assertSame($this->_length, strlen($string1));
			$this->assertSame($this->_length, strlen($string2));

			$this->assertNotEquals($string1, $string2);
			$this->assertRegExp('/^[A-Za-z0-9]{' . $this->_length . '}$/', $string1);
			$this->assertRegExp('/^[A-Za-z0-9]{' . $this->_length . '}$/', $string2);
		}
	}

	public function testGenerateNativelyShortLength()
	{
		for ($i = 1; $i < 200; ++$i) {
			$string1 = $this->_stringGenerator->generateNatively(5);
			$string2 = $this->_stringGenerator->generateNatively(5);
			$this->assertSame(5, strlen($string1));
			$this->assertSame(5, strlen($string2));

			$this->assertNotEquals($string1, $string2);
			$this->assertRegExp('/^[A-Za-z0-9]{5}$/', $string1);
			$this->assertRegExp('/^[A-Za-z0-9]{5}$/', $string2);
		}
	}

	public function testGenerateNativelyLongLength()
	{
		for ($i = 1; $i < 200; ++$i) {
			$string1 = $this->_stringGenerator->generateNatively(100);
			$string2 = $this->_stringGenerator->generateNatively(100);
			$this->assertSame(100, strlen($string1));
			$this->assertSame(100, strlen($string2));

			$this->assertNotEquals($string1, $string2);
			$this->assertRegExp('/^[A-Za-z0-9]{100}$/', $string1);
			$this->assertRegExp('/^[A-Za-z0-9]{100}$/', $string2);
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