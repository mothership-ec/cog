<?php

namespace Message\Cog\HTTP\Event;

use Message\Cog\HTTP\Dispatcher;
use Message\Cog\HTTP\Request;

class Event extends \Message\Cog\Event\Event
{
	const REQUEST        = 'http.request';
	const RESPONSE_BUILD = 'http.response.build';
	const RESPONSE       = 'http.response';
	const EXCEPTION      = 'http.exception';

	protected $_dispatcher;
	protected $_request;

	public function __construct(Dispatcher $dispatcher, Request $request)
	{
		$this->_dispatcher = $dispatcher;
		$this->_request    = $request;
	}

	public function getDispatcher()
	{
		return $this->_dispatcher;
	}

	public function getRequest()
	{
		return $this->_request;
	}
}