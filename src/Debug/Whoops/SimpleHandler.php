<?php

namespace Message\Cog\Debug\Whoops;

use Whoops\Handler\Handler;
use InvalidArgumentException;

/**
 * @author Samuel Trangmar-Keates <sam@message.co.uk>
 *
 * This is a simple Whoops handler which causes the exception to pass right through.
 * 
 * On some requests, the PrettyPageHandler causes massive problems and can take
 * an age to load with large stacktraces. This handler is a workaround for those
 * issues.
 */
class SimpleHandler extends Handler
{
	/**
	 * {@inheritDoc}
	 * 
	 * Just throws the exception, meaning it will error in the same way a normal
	 * php app (without whoops) would.
	 */
	public function handle()
	{
		throw $this->getException();
	}
}