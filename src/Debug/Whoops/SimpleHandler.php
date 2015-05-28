<?php

namespace Message\Cog\Debug\Whoops;

use Whoops\Handler\Handler;
use InvalidArgumentException;

class SimpleHandler extends Handler
{
	public function handle()
	{
		throw $this->getException();
	}
}