<?php

namespace Message\Cog\Config;

/**
 * Configuration loader interface.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LoaderInterface
{
	/**
	 * Load configuration groups and add them to the passed registry.
	 *
	 * @param  Registry $registry The registry to add configuration groups to
	 * @return Registry $registry The same registry is returned
	 */
	public function load(Registry $registry);
}