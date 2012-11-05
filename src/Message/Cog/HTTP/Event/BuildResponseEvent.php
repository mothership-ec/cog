<?php

namespace Message\Cog\HTTP\Event;

use Message\Cog\HTTP\Dispatcher;
use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;

abstract class BuildResponseEvent extends Event
{
	protected $_response;

	/**
	 * Get the Response that the listener(s) set on this event.
	 *
	 * @return Response The Response instance
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Sets the Response to be used for the given Request. Calling this will
	 * stop any further events listeners from being called on this event.
	 *
	 * @param Response $response The Response to set
	 */
	public function setResponse(Response $response)
	{
		$this->_response = $response;

		$this->stopPropagation();
	}

	/**
	 * Check whether a Response has been set.
	 *
	 * @return boolean The result of the check
	 */
	public function hasResponse()
	{
		return null !== $this->_response;
	}
}