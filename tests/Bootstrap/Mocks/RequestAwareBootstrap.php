<?php

namespace Message\Cog\Test\Bootstrap\Mocks;

use Message\Cog\Bootstrap\BootstrapInterface;
use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\RequestAwareInterface;

/**
 * This bootstrap implements the `HTTP\RequestAwareInterface`, so we can test
 * that the `Bootstrap\Loader` sets the request on these bootstraps.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RequestAwareBootstrap implements BootstrapInterface, RequestAwareInterface
{
	protected $_request;

	public function setRequest(Request $request)
	{
		$this->_request = $request;
	}

	public function getRequest()
	{
		return $this->_request;
	}
}