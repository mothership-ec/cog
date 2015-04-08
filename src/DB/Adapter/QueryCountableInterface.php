<?php

namespace Message\Cog\DB\Adapter;

/**
 * Interface QueryCountableInterface
 * @package Message\Cog\DB\Adapter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
interface QueryCountableInterface
{
	/**
	 * Count the number of queries that have been run
	 *
	 * @return int
	 */
	public function getQueryCount();

	/**
	 * Get a list of queries that have been run
	 *
	 * @return array
	 */
	public function getQueryList();
}