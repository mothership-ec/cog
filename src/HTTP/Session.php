<?php

namespace Message\Cog\HTTP;

use Symfony\Component\HttpFoundation\Session\Session as BaseSession;

/**
 * A wrapper around Symfony's Session object
 */
class Session extends BaseSession
{
	/**
	 * Extends Symfony's built in class to ensure that the session has already
	 * started when we try and get the ID.
	 *
	 * @return string The session ID
	 */
	public function getId()
	{
		parent::start();

		return parent::getId();
	}
}