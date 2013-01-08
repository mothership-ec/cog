<?php

namespace Message\Cog\Application\Context;

use Message\Cog\Service\ContainerInterface;

/**
 * Interface for running a specific context.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ContextInterface
{
	/**
	 * Run context-specific code.
	 *
	 * This is run after Cog has been initialised & bootstrapped and all modules
	 * have been loaded.
	 */
	public function run();
}