<?php

namespace Message\Cog\HTTP\Event;

use Message\Cog\HTTP\Dispatcher;
use Message\Cog\HTTP\Request;
use Message\Cog\HTTP\Response;

/**
 * This event is used for turning an Exception into a Response object.
 */
class BuildResponseFromExceptionEvent extends BuildResponseEvent
{
	protected $_exception;

	/**
	 * Constructor.
	 *
	 * @param Dispatcher $dispatcher The HTTP dispatcher instance
	 * @param Request    $request    The Request instance
	 * @param \Exception $exception  The Exception to be turned into a Response
	 */
	public function __construct(Dispatcher $dispatcher, Request $request, \Exception $exception)
	{
		parent::__construct($dispatcher, $request);

		$this->setException($exception);
	}

	/**
	 * Gets the exception.
	 *
	 * @return mixed The exception
	 */
	public function getException()
	{
		return $this->_exception;
	}

	/**
	 * Sets the exception.
	 *
	 * @return \Exception The exception to set
	 */
	public function setException(\Exception $exception)
	{
		return $this->_exception = $exception;
	}
}