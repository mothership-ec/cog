<?php

namespace Message\Cog\Application\Context;

/**
 * Interface for running a specific context.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface ContextInterface
{
	/**
	 * Constructor. This is for running context-specific initialisation code.
	 *
	 * This is run after Cog has been initialised & bootstrapped, but before any
	 * modules are loaded & bootstrapped.
	 */
	public function __construct();

	/**
	 * Run context-specific code.
	 *
	 * This is run after Cog has been initialised & bootstrapped and all modules
	 * have been loaded.
	 */
	public function run();
}