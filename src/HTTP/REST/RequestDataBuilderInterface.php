<?php

namespace Message\Cog\HTTP\REST;

/**
 * Interface for RequestDataBuilders.
 * Intended for extension to create RequestData objects using data from specific models.
 *
 * Interface RequestDataBuilderInterface
 * @package Message\Cog\HTTP\REST
 */
interface RequestDataBuilderInterface
{
	/**
	 * Get fully populated instance of RequestData
	 *
	 * @return RequestData
	 */
	public function getRequestData();
}