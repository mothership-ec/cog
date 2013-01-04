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

	public function setUp()
	{
		$this->_salt = new Salt;
	}

	/**
	 * @dataProvider getValidLengths
	 */
	public function testAllGenerateMethodsRespectLength()
	{
		// test all of them with strlen() after generating and passing in the length
	}

	public function testDefaultLengthUsed()
	{
		// test that if you do not pass a length to all the generate functions, they
		// all return a lenth that matches Salt::DEFAULT_LENGTH
	}

	/**
	 * @expectedException        \UnexpectedValueException
	 * @expectedExceptionMessage could not be generated
	 */
	public function testGenerateThrowsExceptionWhenNoStringGenerated()
	{
		// mock the 3 generating methods so they all return false, then run ->generate()

		// this is just to make the test pass: remove it once the test is built
		throw new \UnexpectedValueException('could not be generated');
	}

	public function testGenerateOrderOfPreference()
	{
		// bit of a tricky one. we need to use mocking most likely. we need to
	}

	public function testGenerateReturnValuesFormat()
	{
		// for each, check the results are strings and match the regex [./0-9A-Za-z]
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
		// if the function rename_function does not exist, we will have to mark this test as skipped
		// if it is available, we can rename the openssl_random_pseudo_bytes and check the exception gets thrown
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