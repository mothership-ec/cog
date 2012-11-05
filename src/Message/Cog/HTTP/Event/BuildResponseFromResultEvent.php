<?php

namespace Message\Cog\HTTP\Event;

use Message\Cog\HTTP\Dispatcher;
use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;

class BuildResponseFromResultEvent extends BuildResponseEvent
{
	protected $_result;

	/**
	 * Constructor.
	 *
	 * @param Dispatcher $dispatcher The HTTP dispatcher instance
	 * @param Request    $request    The Request instance
	 * @param mixed      $result     The result from the request being executed (controller result)
	 */
	public function __construct(Dispatcher $dispatcher, Request $request, $result)
	{
		parent::__construct($dispatcher, $request);

		$this->_result = $result;
	}

	/**
	 * Get the result returned from the request's execution (controller result)
	 *
	 * @return mixed The request's execution result
	 */
	public function getResult()
	{
		return $this->_result;
	}
}