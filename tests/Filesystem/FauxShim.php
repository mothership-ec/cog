<?php

namespace Message\Cog\Test\Filesystem;

use Message\Cog\Filesystem\StreamWrapperShim;

class FauxShim extends StreamWrapperShim
{
	public function __construct()
	{

	}
	
	public function getStreamWrapperPrefix()
	{
		return 'test';
	}
}