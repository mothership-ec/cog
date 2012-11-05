<?php

namespace Message\Cog\HTTP\Event;

use Message\Cog\HTTP\Dispatcher;
use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;

class FilterResponseEvent extends Event
{
	protected $_response;

	/**
	 * Constructor.
	 *
	 * @param Dispatcher $dispatcher The HTTP dispatcher instance
	 * @param Request    $request    The Request instance
	 * @param Response   $result     The Response instance
	 */
	public function __construct(Dispatcher $dispatcher, Request $request, Response $response)
	{
		parent::__construct($dispatcher, $request);

		$this->_response = $response;
	}

	/**
	 * Get the Response.
	 *
	 * @return Response The Response instance
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Sets a new Response to be used.
	 *
	 * @param Response $response The Response to set
	 */
	public function setResponse(Response $response)
	{
		$this->_response = $response;
	}
}