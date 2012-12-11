<?php

namespace Message\Cog\Test\Application\Context;

use Message\Cog\Application\Context\ContextInterface;

/**
 * An empty implementation of `Application\Context\ContextInterface` to aid
 * unit testing.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FauxContext implements ContextInterface
{
	/**
	 * Run the application context.
	 *
	 * This currently doesn't do anything as we don't need or want it to when
	 * unit testing.
	 */
	public function run()
	{

	}
}