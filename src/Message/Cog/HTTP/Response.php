<?php

namespace Message\Cog\HTTP;

/**
 * Our HTTP Response class. Extends Symfony's.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Response extends \Symfony\Component\HttpFoundation\Response
{
	/**
	 * Checks whether this Response is either a client or server error.
	 *
	 * @return boolean Result of the check
	 */
	public function isError()
	{
		return $this->isClientError() || $this->isServerError();
	}
}