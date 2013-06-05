<?php

namespace Message\Cog\Test\Filesystem;

use Message\Cog\Filesystem\File;
use Message\Cog\Filesystem\StreamWrapperManager;
use Message\Cog\Filesystem\StreamWrapper;

class FileTest extends \PHPUnit_Framework_TestCase
{
	protected function setUp()
	{
		$this->manager = new StreamWrapperManager();
		$this->manager->register('cog', function(){
			$wrapper = new StreamWrapper;
			$wrapper->setMapping(array(
				"/^\/public\/(.*)/us" => __DIR__.'/fixtures/tmp/$1',
				"/^\/tmp\/(.*)/us" => __DIR__.'/fixtures/tmp/$1',
			));

			return $wrapper;
		});
	}

	protected function tearDown()
	{
		$this->manager->clear();
	}


	public function testChecksum()
	{
		$path = __DIR__.'/fixtures/tmp/hello.txt';
		$file = new File($path);

		$this->assertSame('7d793037a0760186574b0282f2f435e7', $file->getChecksum());
	}

	public function testGettingPublicUrl()
	{
		$file = new File('cog://public/hello.txt');

		$this->assertSame('/hello.txt', $file->getPublicUrl());
	}

	/**
	 * @expectedException \Exception
	 */
	public function testGettingNonPublicUrl()
	{
		$file = new File('cog://tmp/hello.txt');

		$file->getPublicUrl();
	}

	public function testIsPublic()
	{
		$file = new File('cog://tmp/hello.txt');
		$this->assertFalse($file->isPublic());

		$file = new File('cog://public/hello.txt');
		$this->assertTrue($file->isPublic());
	}

}