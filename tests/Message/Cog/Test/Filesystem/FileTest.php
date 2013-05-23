<?php

namespace Message\Cog\Test\Filesystem;

use Message\Cog\Filesystem\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
	public function testChecksum()
	{
		$path = __DIR__.'/fs/tmp/hello.txt';
		$file = new File($path);

		$this->assertSame('7d793037a0760186574b0282f2f435e7', $file->getChecksum());
	}
}