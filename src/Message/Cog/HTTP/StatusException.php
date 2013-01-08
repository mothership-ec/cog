<?php

namespace Message\Cog\HTTP;

/**
 * Exceptions that represent a specific HTTP response status code.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StatusException extends \Exception
{
	const FORBIDDEN           = 303;

	const NOT_FOUND           = 404;
	const NOT_ALLOWED         = 405;
	const NOT_ACCEPTABLE      = 406;

	const SERVER_ERROR        = 500;
	const SERVICE_UNAVAILABLE = 503;
}