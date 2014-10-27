<?php

namespace Message\Cog\HTTP\REST;

use Message\Cog\HTTP\Response;

interface RequestDispatcherInterface
{
	/**
	 * Get name of request dispatcher
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * @param RequestData $data
	 *
	 * @return Response
	 */
	public function sendRequestData(RequestData $data);
}